<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 26.11.17
 * Time: 23:22
 */

namespace AppBundle\Service;


class SearchMen
{
    private $siteUrl = 'http://www.charmdate.com';

    private $agent;
    private $staff;
    private $password;

    /**
     * @var int
     */
    private $menLimit = 25;

    /**
     * @var string
     */
    private $ladyId = "C999099";

    /**
     * @var string
     */
    private $cookie;

    /**
     * @param int $menLimit
     * @param string $agent
     * @param string $staff
     * @param string $password
     * @return array
     */
    public function searchMen(int $menLimit, string $agent, string $staff, string $password)
    {
        $this->menLimit = $menLimit;
        $this->agent = $agent;
        $this->staff = $staff;
        $this->password = $password;

        $this->cookie = $this->login();

        $menWithCredits = $this->getMenWithCredits();

        foreach ($menWithCredits as $manWithCredit) {
            $this->sendEmail($manWithCredit, $this->ladyId);
        }

        return $menWithCredits;
    }

    /**
     * @return string
     */
    private function login(): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->siteUrl."/clagt/login.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            "agentid=" . $this->agent . "&staff_id=" . $this->staff . "&passwd=" . $this->password . "&agentlogin=Login"
        );

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Upgrade-Insecure-Requests: 1',
                'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                'Origin:'.$this->siteUrl,
                'Connection:keep-alive',
                'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
            ]
        );

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = curl_exec($ch);

        $header = explode("\r\n\r\n", $response, 2)[0];

        preg_match('/PHPSESSID=(\w+);/', $header, $matches);

        curl_close($ch);

        $cookie = $matches[1];

        return $cookie;
    }

    /**
     * @return array
     */
    private function getMenWithCredits(): array {
        $menWithCredits = [];

        do {
            $menWithCredits = array_merge(
                $menWithCredits,
                $this->getMenWithCreditsByIds($this->getMenIdsByPage($page))
            );

            if (count($menWithCredits) >= $this->menLimit) {
                break;
            }

        } while (!empty($page));

        return $menWithCredits;
    }

    /**
     * @param $page
     * @return array
     */
    private function getMenIdsByPage(&$page): array
    {
        $ch = curl_init();

        $now = new \DateTime();

        $loginParams = [
            'womanId' => $this->ladyId,
            'ddddddddddddddddd' => 1,
            'age1' => 1983,
            'age2' => 1955,
            'country' => '',
            'dateType' => 'last_login',
            'year_s' => $now->format('Y'),
            'month_s' => $now->format('m'),
            'day_s' => $now->format('d'),
            'year_e' => $now->format('Y'),
            'month_e' => $now->format('m'),
            'day_e' => $now->format('d'),
            'photo' => 'N',
            'birthday' => 0,
            'Submit' => 'Search',
        ];

        if (!empty($page)) {
            $loginParams['page'] = $page;
        }

        curl_setopt(
            $ch,
            CURLOPT_URL,
            $this->siteUrl."/clagt/admire/search_matches3.php?".http_build_query($loginParams)
        );

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Upgrade-Insecure-Requests: 1',
                'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                'Origin:'.$this->siteUrl,
                'Connection:keep-alive',
                'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
                'Cookie:PHPSESSID='.$this->cookie.'; CD_Change_Screen=1920;'
            ]
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        preg_match_all('/men_profile.php\?manid=(\w+)&/', $response, $matches);
        $manIds = array_unique($matches[1]);
        sort($manIds);

        preg_match('/page=(.+?)"/', $response, $pageMatches);

        $page = $pageMatches[1];

        curl_close($ch);

        return $manIds;
    }

    /**
     * @param $menIds
     * @return array
     */
    private function getMenWithCreditsByIds($menIds): array
    {
        $menWithCredits = [];

        foreach ($menIds as $manId) {
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                $this->siteUrl."/clagt/admire/men_profile.php?manid=".$manId."&womanid=".$this->ladyId
            );

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Upgrade-Insecure-Requests: 1',
                    'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                    'Origin:'.$this->siteUrl,
                    'Connection:keep-alive',
                    'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
                    'Cookie:PHPSESSID='.$this->cookie
                ]
            );

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);

            curl_close($ch);

            preg_match('/Credit: (.+?)<\/td>\s*?<td>(.+?)<\/td>/', $response, $matchesCredit);

            if ($matchesCredit[2] == 'Yes') {
                $menWithCredits[] = $manId;
            }

            if (count($menWithCredits) >= $this->menLimit) {
                break;
            }
        }

        return $menWithCredits;
    }

    /**
     * @param string $manId
     * @param string $womanId
     */
    private function sendEmail(string $manId = 'CM32985282', string $womanId = 'C999099')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->siteUrl."/clagt/admire/send_admire_mail2.php");
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Upgrade-Insecure-Requests: 1',
                'Origin:'.$this->siteUrl,
                'Connection:keep-alive',
                'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
                'Referer:'.$this->siteUrl.'/clagt/admire/send_admire_mail.php?admire_type=T&admire_category=B&manid='.$manId.'&womanid='.$womanId,
                'Cookie:PHPSESSID='.$this->cookie,
            ]
        );

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_exec($ch);

        curl_close($ch);
    }
}