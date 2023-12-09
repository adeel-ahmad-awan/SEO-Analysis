<?php

namespace App\Command;

use App\Repository\PageRepository;
use App\Service\PageService;
use PHPUnit\Util\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recheck-webpages',
    description: 'command for re checking all the pages and update its required information',
)]
class RecheckWebpagesCommand extends Command
{
    /**
     * @var PageRepository
     */
    private PageRepository $pageRepository;

    /**
     * @var PageService
     */
    private PageService $pageService;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PageRepository $pageRepository
     * @param PageService $pageService
     * @param LoggerInterface $logger
     */
    public function __construct(
        PageRepository $pageRepository,
        PageService $pageService,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->pageRepository = $pageRepository;
        $this->pageService = $pageService;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Re-checks all webpage URLs and updates information in the database every hour.')
            ->setHelp('This command is scheduled to run periodically to ensure that information about webpages is up-to-date in the database.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $pages = $this->pageRepository->findAll();
            $progressBar = new ProgressBar($output, count($pages));
            $progressBar->start();

            // Loop through each page and recheck its contents
            foreach ($pages as $page) {
                try {
                    $pageStatus = $this->pageService->recheckPage($page);
                    $io->success($pageStatus."\n");
                    $progressBar->advance();
                } catch (\Exception $exception) {
                    $io->error($exception->getMessage());
                }
            }
            $progressBar->finish();
            $io->success("All pages rechecked.");
            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred in app:recheck-webpages.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
        }
        $io->error("An error occurred in app:recheck-webpages command.");
        $io->error("Pleas check the log file for details.");
        return Command::FAILURE;
    }
}
