<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PageController extends Controller
{
    public function legalNotice(): array
    {
        return $this->render(
            'pages/legal-notice',
            'Mentions légales',
            $this->legalData()
        );
    }

    public function terms(): array
    {
        return $this->render(
            'pages/terms',
            'Conditions générales de vente',
            $this->legalData()
        );
    }

    public function privacy(): array
    {
        return $this->render(
            'pages/privacy',
            'Politique de confidentialité',
            $this->legalData()
        );
    }

    /** @return array<string, string> */
    private function legalData(): array
    {
        return [
            'companyLegalName' => getenv('COMPANY_LEGAL_NAME')
                ?: 'Vite & Gourmand',
            'companyAddress' => getenv('COMPANY_ADDRESS')
                ?: 'Bordeaux, France',
            'companySiret' => getenv('COMPANY_SIRET') ?: '',
            'companyEmail' => getenv('COMPANY_EMAIL')
                ?: 'contact@vite-et-gourmand.fr',
        ];
    }
}
