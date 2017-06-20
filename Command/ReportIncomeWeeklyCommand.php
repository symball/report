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
class ReportIncomeWeeklyCommand extends ContainerAwareCommand
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
        ->setName('report:income:weekly')
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

        $container = $this->getContainer();
        $reportBuilder = $container->get('symball_report.report_builder');

        // Because it's going to be used in the data processing stage as well as the
        // report builder service, assign it to a variable
        $machineCheckinRepository = $container->get('repository.machinecheckin');

        $numberOfWeeksPastToUse = 4;

        $startDateSet = (
        new \DateTime())
        ->sub((
        new \DateInterval('P' . $numberOfWeeksPastToUse . 'W')));

        $reportQuery = new QueryTimeInterval();
        $reportQuery
        ->setRepository($machineCheckinRepository)
        ->setHeadDateTime($startDateSet)
        ->setIntervalDateTime(new \DateInterval('P1W'))
        ->setNumberDataSets(3);

        $reportBuilder->setQuery($reportQuery);

        $meta = $reportBuilder->meta();
        $meta
        ->setOption(['column_auto_width' => false])
        ->column(
            'amount_reported',
            0,
            ['title' => 'rpt', 'display_options' => ['highlight_negative']]
        )
        ->column(
            'amount_taken',
            0,
            ['title' => 'tkn', 'display_options' => ['highlight_negative']]
        )
        ->column(
            'tally',
            0,
            ['title' => 'cnt']
        )
        ->column(
            'difference_reported',
            0,
            ['title' => 'drpt', 'display_options' => ['highlight_negative', 'highlight_positive']]
        )
        ->column(
            'difference_taken',
            0,
            ['title' => 'dtkn', 'display_options' => ['highlight_negative', 'highlight_positive']]
        );

        $reportBuilder->createSheet('Income Weekly');

        // Start creation
        while ($reportBuilder->newSet()) {
            $output->writeln('new set');
            $reportBuilder->pattern('setHeadings');

            $unprocessedData = $reportBuilder->query()->run();
            foreach ($unprocessedData as $machineCollection) {
                // Checks the data piece has necessary points set
                $franchise = $machineCollection->getFranchise();
                if (!$franchise || !$franchise->getSlug()) {
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
                }

                $reportBuilder->meta
                ->setPoint($franchise->getSlug())
                ->increment('amount_taken', $machineCollection->getAmountTaken())
                ->increment('amount_reported', $machineCollection->getAmountReported())
                ->increment('tally', 1)
                ->increment('difference_reported', $changeReported)
                ->increment('difference_taken', $changeTaken);
            } // End processing data set unprocessed records

            // Draw the current data set
            $reportBuilder->pattern('dataSet');
        }

        $reportBuilder->pattern('dataPointIndex');

        $fileName = 'income-weekly-' . date('y-m-d') . '.xls';
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
