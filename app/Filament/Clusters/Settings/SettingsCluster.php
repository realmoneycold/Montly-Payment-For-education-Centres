<?php

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    // Temporarily hidden from navigation (kept for future re-enable)
    public static function shouldRegisterNavigation(): bool
    {
        // Temporarily disabled - remove this method entirely in future to re-enable
        // return auth()->user()?->hasRole('admin') ?? false;
        return false;
    }

    protected static ?int $navigationSort = 9999;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected string $view = 'filament.clusters.settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function mount(): void
    {
        // Show the index page instead of redirecting to the first child
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Settings');
    }

    // Original navigation group (kept for future re-enable):
    // public static function getNavigationGroup(): ?string
    // {
    //     return __('Settings');
    // }

    public static function getNavigationGroup(): ?string
    {
        // Temporarily moved out of side navigation (kept for future)
        return null;
    }
}
