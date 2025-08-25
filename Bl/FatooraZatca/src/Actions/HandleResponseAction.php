<?php

namespace Bl\FatooraZatca\Actions;

use Exception;

class HandleResponseAction
{
    const HTTP_OK = 200;

    const HTTP_ACCEPTED = 202;

    const HTTP_UNAUTHORIZED = 401;

    /**
     * handle the response of zatca portal.
     *
     * @param  mixed  $httpcode
     * @param  mixed  $response
     */
    public function handle($httpcode, $response): array
    {
        if (in_array((int) $httpcode, [self::HTTP_OK, self::HTTP_ACCEPTED])) {

            return $response;

        } elseif ((int) $httpcode === self::HTTP_UNAUTHORIZED) {

            throw new Exception('Unauthoroized zatca settings!');
        }

        throw new Exception(
            empty($response)
            ? 'Unhandeled zatca error exception!'
            : json_encode($response)
        );
    }
}
