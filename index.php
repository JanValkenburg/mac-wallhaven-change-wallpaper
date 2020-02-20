<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class App
{
    protected $api_key = '5VT3N7UoLwXIADhMrQ31sv2G7DXJXR7N';
    protected $sorting = 'random';
    protected $tmpFile = '/Users/${USER}/wallpaper.png';
    protected $screens = 'all';
    protected $query = 'anime girl';
    protected $purity = 'sfw';
    protected $resolutions = '1920x1080';
    protected $topRange = null;
    protected $images = [];
    protected $categories;

    /**
     * @throws Exception
     */
    function __destruct()
    {
        $interval = $_GET['interval'] ?? 900;
        $time = (new DateTime('+'.$interval.' SECONDS'))->format('H:i');
        require 'view/index.php';
    }

    public function run()
    {
        $userId = trim(shell_exec('id -un'));
        $this->tmpFile = str_replace('${USER}', $userId, $this->tmpFile);

        $this->query = $_GET['q'] ?? $this->query;
        $this->resolutions = $_GET['resolution'] ?? $this->resolutions;
        $this->purity = $_GET['purity'] ?? $this->purity;
        $this->topRange = $_GET['topRange'] ?? $this->topRange;
        $this->categories = $_GET['categories'] ?? $this->categories;

        $resolutions = explode(',', $this->resolutions);
        foreach ($resolutions as $screen => $resolution) {
            $this->screens = $screen;
            $this->resolutions = $resolution;
            $this->handleImage();
        }
    }

    protected function handleImage()
    {
        if (trim($this->topRange) != '') {
            $query = http_build_query([
                'apikey' => $this->api_key,
                'resolutions' => $this->resolutions,
                'sorting' => $this->sorting,
                'topRange' => $_GET['topRange'] ?? null,
                'purity' => $this->purity
            ]);
        } else {
            $query = http_build_query([
                'apikey' => $this->api_key,
                'resolutions' => $this->resolutions,
                'q' => $this->query,
                'sorting' => $this->sorting,
                'categories' => $this->categories,
                'purity' =>  $this->purity
            ]);
        }

        $url = 'https://wallhaven.cc/api/v1/search?' . $query;
        $content = file_get_contents($url);
        $data = json_decode($content);

        if (false === isset($data->data[0]->path)) {
            echo '<br>';
            echo 'no image found: ' . $this->query . ' ' . $this->resolutions;
            return null;
        }
        $this->images[] = $data->data[0];

        file_put_contents($this->tmpFile, file_get_contents($data->data[0]->path));
        shell_exec('/usr/local/bin/wallpaper set --screen ' . $this->screens . ' ' . $this->tmpFile);
    }

}

(new App())->run();