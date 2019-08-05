<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserResetMonthScoreCommand extends Command {
    protected static $defaultName = 'user:reset-month-score';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;


    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    protected function configure() {
        $this
            ->setDescription('Reset Monthly User score')
            ->addArgument('userid', InputArgument::OPTIONAL, 'User ID')
            ->addOption('dry', null, InputOption::VALUE_NONE, 'Log everything without deleting');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $userid = $input->getArgument('userid');

        if ($userid) {
            if ($user = $this->userRepository->find($userid)) {
                $user->setMonthScore(0);
                $io->writeln('Score reset for : ' . $user->getUsername());
            } else {
                $io->error("Utilisateur introuvable");
            }
        } else {
            foreach ($this->userRepository->findAll() as $user) {
                $user->setMonthScore(0);
                $io->writeln('Score reset for : ' . $user->getUsername());
            }
        }

        if (!$input->getOption('dry')) {
            $this->entityManager->flush();
        }

        $io->success('Reset Finished');
    }
}
