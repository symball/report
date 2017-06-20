<?php
/**
 * Definition for the Integrity checkins command.
 */

namespace Symball\ReportBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symball\ReportBundle\Service\QueryTimeInterval;

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
class ReportProblemCollectionsCommand extends ContainerAwareCommand
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
        ->setName('report:problem:collections')
        ->setDescription('Create a daily report Boomtown profits by Franchise')
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
        /* Get all users marked for deletion */
        $output->writeln('--- Problematic checkins for the day ---');

        // Start event named 'eventName'

        $container = $this->getContainer();

        $reportBuilder = $container->get('symball_report.report_builder');

        // Because it's going to be used in the data processing stage as well as the
        // report builder service, assign it to a variable
        $machineCheckinRepository = $container->get('repository.machinecheckin');

        $numberDaysPast = '20';
        // One day past
        $startDateSet = (new \DateTime())->modify('-' . $numberDaysPast . ' days');

        $meta = $reportBuilder->meta();
        $meta
        ->column('date')
        ->column('franchise')
        ->column('collector')
        ->column('difference_reported', '', ['title' => 'Difference (%)'])
        ->column('amount_reported', 0)
        ->column('amount_taken', 0)
        ->column('last_amount_reported', 0)
        ->column('last_amount_taken', 0);

        $reportQuery = new QueryTimeInterval();
        $reportQuery
        ->setRepository($machineCheckinRepository)
        ->setHeadDateTime($startDateSet)
        ->setIntervalDateTime(new \DateInterval('P' . $numberDaysPast . 'D'));
        $reportBuilder->setQuery($reportQuery);

        $reportBuilder->createSheet('Problematic collections');
        // Start creation and get data
        $output->writeln('start processing data sets');
        $reportBuilder->newSet();

        $unprocessedData = $reportBuilder->query()->run();

        $output->writeln('Cycling through ' . $unprocessedData->count() . ' records');
        foreach ($unprocessedData as $machineCollection) {
            if (!$machineCollection->getMachine()) {
                continue;
            }

            // Get the previous checkin for the machine
            $lastCheckin = $machineCheckinRepository->findOneByPreviousCheckin(
                $machineCollection->getMachine(),
                $machineCollection->getCreatedAt()
            );

            if ($lastCheckin) {
                  /* Work out the difference in amount from last checkin */
                $changeReported = $machineCollection->getAmountReported() - $lastCheckin->getAmountReported();
                  $changeTaken = $machineCollection->getAmountTaken() - $lastCheckin->getAmountTaken();

            /* Is there reason to create a flagged collection */
                if ($changeReported > 0) {
                      $differencePercentage = (($machineCollection->getAmountTaken() - $changeReported) / $changeReported) * 100;

                    /* If change is greater than -3% drop, flag checkin */
                    if ($differencePercentage < -3) {
                        $differencePercentage = (string) abs(round($differencePercentage, 2));
                        // $output->writeln((string) $machineCollection->getMachine().' difference reported: '.$differencePercentage);

                        $meta
                        ->setPoint((string) $machineCollection->getMachine())
                        ->set('franchise', (string) $machineCollection->getFranchise())
                        ->set('collector', (string) $machineCollection->getCollector())
                        ->set('amount_taken', $machineCollection->getAmountTaken())
                        ->set('amount_reported', $machineCollection->getAmountReported())
                        ->set('date', $machineCollection->getCreatedAt()->format('y-m-d H:i'))
                        ->set('difference_reported', $differencePercentage)
                        ->set('last_amount_reported', $lastCheckin->getAmountReported())
                        ->set('last_amount_taken', $lastCheckin->getAmountTaken());
                    }
                }
            }
        } // End processing data set unprocessed records

        $output->writeln('Finished data Processing with ' . $meta->dataCount() . ' records to be placed on excel');

        // Draw the current data set
        $reportBuilder->pattern('setHeadings');
        $reportBuilder->pattern('dataSet');
        $reportBuilder->pattern('dataPointIndex');

        $fileName = 'problem-collections-' . date('y-m-d') . '.xls';
        $prefix = $input->getArgument('report_prefix');
        if ($prefix) {
            $fileName = $prefix . '-' . $fileName;
        }
        $file = $reportBuilder->save($fileName);

        // Write the report to S3
        $container->get('knp_gaufrette.filesystem_map')->get('reports')
        ->write($fileName, file_get_contents($file->getPathName()), true);
    }
}
