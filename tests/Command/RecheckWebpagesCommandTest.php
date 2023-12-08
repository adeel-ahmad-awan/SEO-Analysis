<?php

namespace App\Tests\Command;

use App\Command\RecheckWebpagesCommand;
use App\Repository\PageRepository;
use App\Service\PageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RecheckWebpagesCommandTest
 */
class RecheckWebpagesCommandTest extends TestCase
{
    /**
     * @return void
     */
    public function testExecute()
    {
        $pageRepository = $this->createMock(PageRepository::class);
        $pageService = $this->createMock(PageService::class);

        $pages = [];

        $pageRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($pages);

        $pageService->expects($this->exactly(count($pages)))
            ->method('recheckPage')
            ->willReturn('Page rechecked successfully');

        $command = new RecheckWebpagesCommand($pageRepository, $pageService);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All pages rechecked.', $output);

        $this->assertEquals(RecheckWebpagesCommand::SUCCESS, $commandTester->getStatusCode());
    }
}