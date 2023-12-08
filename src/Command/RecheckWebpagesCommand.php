<?php

namespace App\Command;

use App\Repository\PageRepository;
use App\Service\PageService;
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
    description: 'Add a short description for your command',
)]
class RecheckWebpagesCommand extends Command
{
    private PageRepository $pageRepository;

    private PageService $pageService;

    public function __construct(PageRepository $pageRepository, PageService $pageService)
    {
        parent::__construct();
        $this->pageRepository = $pageRepository;
        $this->pageService = $pageService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Re-checks all webpage URLs and updates information in the database every hour.')
            ->setHelp('This command is scheduled to run periodically to ensure that information about webpages is up-to-date in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Fetch all pages from the database
        $pages = $this->pageRepository->findAll();

        // Create a progress bar
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
    }
}
