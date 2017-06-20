<?php
/**
 * Definition for the Integrity checkins command.
 */

namespace Symball\ReportBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symball\ReportBundle\Service\Query;

/**
 * Debug Create Record
 * Used to make sure that referenced documents have been set and exist
 * because MongoDB doesn't enforce referential integrity.
 *
 * @category Command
 *
 * @author   Simon Ball
 *
 * @uses \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
 * @uses \Symfony\Component\Console\Input\InputArgument
 * @uses \Symfony\Component\Console\Input\InputInterface
 * @uses \Symfony\Component\Console\Output\OutputInterface
 */
class ReportProblemOverduecollectionsCommand extends ContainerAwareCommand
{
    /**
   * Auditor service for logging problems to storage.
   *
   * @var object
   */
    private $auditor;

    /**
     * Define how the command is accessed.
     */
    protected function configure()
    {
        parent::configure();

        $this
        ->setName('report:problem:overduecollections')
        ->setDescription('Create a daily report Boomtown which highlights machines without checkins')
        ;
    }

  /**
   * The actual routine that will be run when the command is launched.
   *
   * @param InputInterface  $input  Parameter pickup
   * @param OutputInterface $output Where to send any defined output
   */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(__CLASS__);
        die();
        /* Get all users marked for deletion */
        $output->writeln('--- Beginning missing checkin report builder ---');

        $container = $this->getContainer();

        /* Date time intervals to be considered late */
        $fiveDaysPast = (new \DateTime())->modify('-5 days');
        $eightDaysPast = (new \DateTime())->modify('-8 days');

        $reportBuilder = $container->get('symball_report.report_builder');

        // Prepare the report query
//        $doctrine = $container->get('doctrine.odm.mongodb.document_manager');
        $machineRepository = $container->get('repository.machine');
        $reportQuery = new Query();
        $reportQuery->setRepository($machineRepository);

        // Some tags to be used as conditionals
        $activeTag = $container->get('repository.tag')
        ->findOneBySlugAndType('active', 'machine-status');
        $homeTag = $container->get('repository.tag')
        ->findOneBySlugAndType('home', 'machine-status');
        $tagIds = [
            $activeTag->getId(),
            $homeTag->getId()
        ];

        // The base query to be used
        $qb = $machineRepository->createQueryBuilder();
        $qb->addAnd(
            $qb->expr()
            ->field('condition.id')->in($tagIds)
        );
//            ->addOr(
//                $qb->expr()
//                ->field('condition')->references($homeTag)));

        $output->writeln('hello darkness my old friend');

        $reportQuery
        ->setQueryBase($qb)
        ->addModifier('responsibleFranchise', 'REFERENCES');

        $reportBuilder->setQuery($reportQuery);

        // Prepare the meta information to be used
        $meta = $reportBuilder->meta();
        $meta->setOption([
            'bg_warning' => 'b2dcf7',
            'bg_danger' => 'ef6a6a'
        ]);
        $meta
        ->column('business')
        ->column('contacts')
        ->column('collector')
        ->column('status')
        ->column('last_collection')
        ->column('last_collection_amount_taken')
        ->column('last_collection_amount_reported');

        // Start looping through the various franchise
        $franchises = $container->get('repository.franchise')->findAll();
        foreach ($franchises as $franchise) {
            $reportBuilder->createSheet((string) $franchise);
            $output->writeln('start processing data sets');

            $reportBuilder->newSet();
            $data = $reportBuilder->query()
            ->setModifierValue('responsibleFranchise', $franchise)
            ->run();

            $output->writeln($data->count());

            foreach ($data as $machine) {
                $lastCollection = $machine->getLastCheckin();

                // conditions for skipping this machine
                if (!$lastCollection) {
                    continue;
                }
                if ($lastCollection->getCreatedAt() > $fiveDaysPast) {
                    continue;
                }
                if (!$machine->getResponsibleFranchise()) {
                    continue;
                }

                // Determine the warning level of this machine
                $level = ($lastCollection->getCreatedAt() < $eightDaysPast) ? 'danger' : 'warning';

                $franchiseKey = $machine->getResponsibleFranchise()->__toString();
                if (!isset($dataMap[$franchiseKey])) {
                    $output->writeln('Creating map part for: ' . $franchiseKey);
                    $dataMap[$franchiseKey] = array();
                }

                $contacts = '';
                $business = '';
                $status = $machine->getCondition() ? $machine->getCondition()->__toString() : 'NA';
                $collector = $machine->getResponsiblePerson() ? $machine->getResponsiblePerson()->__toString() : 'NA';

                if ($businessPlaced = $machine->getBusinessPlaced()) {
                    $iContacts = $businessPlaced->getContacts();
                    foreach ($iContacts as $contact) {
                        $contacts .= $contact->__toString();
                    }
                    $business = $businessPlaced->__toString();
                }

                $reportBuilder->meta
                ->setPoint($machine->getSerialNumber())
                ->set('business', $business)
                ->set('contacts', $contacts)
                ->set('collector', $collector)
                ->set('status', $status)
                ->set('last_collection', $lastCollection->getCreatedAt()->format('d/M/y - H:i'))
                ->set('last_collection_amount_taken', $lastCollection->getAmountTaken())
                ->set('last_collection_amount_reported', $lastCollection->getAmountReported());
            }
            $reportBuilder->pattern('setHeadings');
            $reportBuilder->pattern('dataSet');
            $reportBuilder->pattern('dataPointIndex');
        }

        $fileName = 'problems-overdue-collections-' . date('y-m-d') . '.xls';
        $prefix = $input->getArgument('report_prefix');
        if ($prefix) {
            $fileName = $prefix . '-' . $fileName;
        }
        $file = $reportBuilder->save($fileName);

        // Write the report to S3
//        $container->get('knp_gaufrette.filesystem_map')->get('reports')
//        ->write($fileName, file_get_contents($file->getPathName()), true);
    }
}
