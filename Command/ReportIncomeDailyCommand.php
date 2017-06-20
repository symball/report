<?php
/**
 * Definition for the Integrity checkins command.
 */

namespace Symball\ReportBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;

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
class ReportIncomeDailyCommand extends ContainerAwareCommand
{
    /**
     *
     * Symfony command setup
     */
    protected function configure()
    {
        
        $this
        ->setName('report:income:daily')
        ->setDescription('Create a daily report Boomtown profits by Franchise')
        ->addArgument('report_prefix', InputArgument::OPTIONAL, 'prefix')
        ;
    }

  /**
   * @param InputInterface  $input  Parameter pickup
   * @param OutputInterface $output Where to send any defined output
   */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get all users marked for deletion
        $output->writeln('--- Beginning daily report builder ---');

        $container = $this->getContainer();
        $reportBuilder = $container->get('symball_report.report_builder');
        $reportPatterns = $container->get('symball_report.pattern');

        // Because it's going to be used in the data processing stage as well as the
        // report builder service, assign it to a variable
        $machineCheckinRepository = $container->get('repository.machinecheckin');

        $numberOfDaysPastToUse = date('j');
        $startDateSet = (new \DateTime())->sub(
            (new \DateInterval('P' . (50) . 'D'))
        );
        $dateTimeInterval = 'P1D';

        $output->writeln('Report with ' . $numberOfDaysPastToUse . ' data sets');
        $output->writeln('Starting from ' . $startDateSet->format('y-m-d H:i'));
        $output->writeln('Usting the following DateTime interval: ' . $dateTimeInterval);

        $output->writeln('Setting up query');
        $reportQuery = new QueryTimeInterval();
        $reportQuery
        ->setRepository($machineCheckinRepository)
        ->setHeadDateTime($startDateSet)
        ->setIntervalDateTime(new \DateInterval($dateTimeInterval))
        ->setNumberDataSets((int) $numberOfDaysPastToUse)
        ->setDateTimeField('createdAt');
        $reportBuilder->setQuery($reportQuery);

        $output->writeln('Setting up column headings');
        $reportBuilder->meta()
        ->setOption('column_auto_width', false)
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

        $reportBuilder->createSheet('Boomtown daily income');
        // Start creation and get data
        $output->writeln('start processing data sets');
        while ($reportBuilder->newSet()) {
            $unprocessedData = $reportBuilder->query()->run();
            $output->writeln('Set Title: '.(string) $reportBuilder->query());
            
            $reportPatterns->run('set_headings', $reportBuilder);

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

                $changeReported = $machineCollection->getAmountReported();
                $changeTaken = $machineCollection->getAmountTaken();
                if ($lastCheckin) {
                    // Work out the difference in amount from last checkin
                    $changeReported = $machineCollection->getAmountReported() - $lastCheckin->getAmountReported();
                    $changeTaken = $machineCollection->getAmountTaken() - $lastCheckin->getAmountTaken();
                }

                $reportBuilder->meta()
                ->setPoint($franchise->getSlug())
                ->increment('amount_taken', (int) $machineCollection->getAmountTaken())
                ->increment('amount_reported', (int) $machineCollection->getAmountReported())
                ->increment('tally')
                ->increment('difference_reported', (int) $changeReported)
                ->increment('difference_taken', (int) $changeTaken);
            } // End processing data set unprocessed records

            // Draw the current data set
            $reportPatterns->run('data_set', $reportBuilder);
        }

        $reportPatterns->run('data_point_index', $reportBuilder);

        $fileName = 'income-daily-' . date('y-m-d') . '.xls';
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
