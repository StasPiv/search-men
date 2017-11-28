<?php
/**
 * Created by search-men.
 * User: ssp
 * Date: 28.11.17
 * Time: 19:35
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('send:email')
            ->addArgument('manId', null, '', 'CM16835291')
            ->addArgument('womanId', null, '', 'C999099');
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
            ->setAgent($this->getContainer()->getParameter('default_agent'))
            ->setStaff($this->getContainer()->getParameter('default_staff'))
            ->setPassword($this->getContainer()->getParameter('default_password'))
            ->sendEmail($input->getArgument('manId'), $input->getArgument('womanId'));

        print_r($result);

        return null;
    }
}