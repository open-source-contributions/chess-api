<?php

namespace ChessApi\Controller;

use Chess\Movetext;
use Chess\Media\BoardToMp4;
use Chess\Player\PgnPlayer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadMp4Controller extends AbstractController
{
    const MAX_MOVES = 300;

    const OUTPUT_FOLDER = __DIR__.'/../../storage/tmp';

    public function index(Request $request): Response
    {
        $params = json_decode($request->getContent(), true);

        $n = count((new Movetext($params['movetext']))->getMovetext()->moves);
        $movetext = (new Movetext($params['movetext']))->validate();

        if ($movetext && $n <= self::MAX_MOVES) {
            try {
                $board = (new PgnPlayer($movetext))->play()->getBoard();
                $filename = (new BoardToMp4($board))->output(self::OUTPUT_FOLDER);
                $request->attributes->set('filename', $filename);
                return  new BinaryFileResponse(self::OUTPUT_FOLDER.'/'.$filename);
            } catch (\Exception $e) {
                return (new Response())->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return (new Response())->setStatusCode(Response::HTTP_BAD_REQUEST);
    }
}
