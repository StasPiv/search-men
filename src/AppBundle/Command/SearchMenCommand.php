<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchMenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('search:men')
            ->addArgument('menLimit', null, '', 25)
            ->addOption('agent', 'a', InputOption::VALUE_OPTIONAL)
            ->addOption('staff', 's', InputOption::VALUE_OPTIONAL)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL)
            ->setDescription('Search men');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getContainer()->get('app.service.search_men')
                        ->searchMen(
                            $input->getArgument('menLimit'),
                            $input->getOption('agent') ? $input->getOption('agent') : $this->getContainer()->getParameter('default_agent'),
                            $input->getOption('staff') ? $input->getOption('staff') : $this->getContainer()->getParameter('default_staff'),
                            $input->getOption('password') ? $input->getOption('password') : $this->getContainer()->getParameter('default_password')
                        );

        print_r($result);

        return null;
    }
}