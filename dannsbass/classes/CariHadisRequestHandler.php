<?php

namespace Dannsbass\CariHadis;

use Dannsbass\CariHadis;
use Dannsbass\CariHadis\DaftarKitabHadis;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class CariHadisRequestHandler
{
    public $request;
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function ambilRespon(): Response
    {
        $queryParams = $this->request->getQueryParams();

        $body = '<form>Teks: <input type="text" name="q">';
        $body .= '<input type="submit" name="submit" value="Cari"></form><br>';
        $body .= '<h3>Kode kitab:</h3>';
        
        foreach (DaftarKitabHadis::MATAN_TERJEMAH as $key => $kitab) {
            $body .= "$key: <a href='?kitab=$kitab'>$kitab</a><br>";
        }

        $header = ['Content-type' => 'text/html'];

        if (isset($queryParams['q'])) {
            $header = ['Content-type' => 'application/json'];
            $q = htmlspecialchars($queryParams['q']);
            $body = (new CariHadis())->cariKata($q);
        }

        if (isset($queryParams['kitab'])) {
            $header = ['Content-type' => 'application/json'];
            if (isset($queryParams['id'])) {
                $body = (new CariHadis)->cariKitab(htmlspecialchars($queryParams['kitab']), htmlspecialchars($queryParams['id']));
            } else {
                $body = (new CariHadis)->cariKitab(htmlspecialchars($queryParams['kitab']));
            }
        }

        return new Response(
            200,
            $header,
            $body
        );
    }
}
