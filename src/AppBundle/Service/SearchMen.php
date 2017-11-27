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

    public function searchMen(int $menLimit)
    {
        $this->menLimit = $menLimit;
        $this->cookie = $this->login();

        return $this->getMenWithCredits(
            $this->getMenIds($nextPage),
            $nextPage
        );
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
            "agentid=C804&staff_id=S59241&passwd=121427111&agentlogin=Login"
        );

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Upgrade-Insecure-Requests: 1',
                'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                'Origin:'.$this->siteUrl,
                'Connection:keep-alive',
                'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
            )
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
     * @param $nextPage
     * @return array
     */
    private function getMenIds(&$nextPage): array
    {
        $ch = curl_init();

        curl_setopt(
            $ch,
            CURLOPT_URL,
            $this->siteUrl."/clagt/admire/search_matches3.php?womanid=".$this->ladyId."&ddddddddddddddddd=1&age1=1983&age2=1955&country=&dateType=last_login&year_s=2017&month_s=11&day_s=26&year_e=2017&month_e=11&day_e=26&photo=N&birthday=0&Submit=Search"
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            "agentid=C804&staff_id=S59241&passwd=121427111&agentlogin=Login"
        );

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Upgrade-Insecure-Requests: 1',
                'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                'Origin:'.$this->siteUrl,
                'Connection:keep-alive',
                'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
                'Cookie:PHPSESSID='.$this->cookie
            )
        );

// receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        preg_match_all('/men_profile.php\?manid=(\w+)&/', $response, $matches);
        $manIds = array_unique($matches[1]);
        sort($manIds);

        preg_match('/page=(.+?)"/', $response, $pageMatches);

        $nextPage = $pageMatches[1];

        curl_close($ch);

        return $manIds;
    }

    /**
     * @param $menIds
     * @param $nextPage
     * @return array
     */
    private function getMenWithCredits(
        $menIds,
        $nextPage
    ): array {
        $menWithCredits = [];

        do {
            $menWithCredits = array_merge(
                $menWithCredits,
                $this->getMenWithCreditsByIds($menIds)
            );

            if (count($menWithCredits) >= $this->menLimit) {
                break;
            }

            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                $this->siteUrl."/clagt/admire/search_matches3.php?womanid=". $this->ladyId . "&ddddddddddddddddd=1&age1=1983&age2=1955&country=&dateType=last_login&year_s=2017&month_s=11&day_s=26&year_e=2017&month_e=11&day_e=26&photo=N&birthday=0&Submit=Search&page=".$nextPage
            );

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Upgrade-Insecure-Requests: 1',
                    'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                    'Origin:'.$this->siteUrl,
                    'Connection:keep-alive',
                    'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
                    'Cookie:PHPSESSID='.$this->cookie.'; CD_Change_Screen=1920;'
                )
            );

// receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);

            preg_match_all('/men_profile.php\?manid=(\w+)&/', $response, $matches);
            $menIds = array_unique($matches[1]);
            sort($menIds);

            preg_match('/page=(.+?)"/', $response, $pageMatches);

            $nextPage = $pageMatches[1];

            curl_close($ch);

        } while (!empty($nextPage));

        return $menWithCredits;
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
                array(
                    'Upgrade-Insecure-Requests: 1',
                    'Referer:'.$this->siteUrl.'/clagt/loginb.htm',
                    'Origin:'.$this->siteUrl,
                    'Connection:keep-alive',
                    'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
                    'Cookie:PHPSESSID='.$this->cookie
                )
            );

// receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);

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
}