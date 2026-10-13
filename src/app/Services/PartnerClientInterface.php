<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Partner;

interface PartnerClientInterface
{
    /**
     * Отправляет заявку партнёру и возвращает его request_id.
     *
     * @throws \Throwable При ошибке отправки или некорректном ответе.
     */
    public function send(Lead $lead, Partner $partner): string;
}
