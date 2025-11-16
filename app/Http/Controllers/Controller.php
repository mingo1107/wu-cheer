<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function assertSameCompany($companyId): void
    {
        if (auth('api')->check() && isset(auth('api')->user()->company_id)) {
            if ((int) $companyId !== (int) auth('api')->user()->company_id) {
                abort(403, '無權限存取');
            }
        }
    }
}
