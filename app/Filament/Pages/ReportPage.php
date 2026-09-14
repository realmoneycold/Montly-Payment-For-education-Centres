<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

abstract class ReportPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        ini_set('memory_limit', '768M');
    }
}
