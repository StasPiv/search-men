<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchMenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('search:men')
            ->addArgument('menLimit', null, '', 1)
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
        $result = $this->getContainer()->get('app.service.search_men')->searchMen($input->getArgument('menLimit'));

        print_r($result);

        return null;
    }
}