<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HotelBranding
{
    public static function defaults(): array
    {
        return [
            'NamaPT' => 'THE FOREST HOTEL',
            'UsahaPT' => 'HOTEL',
            'FaxPT' => '0251-8212392',
            'AlamatPT' => 'Jl. RE Sumartadireja No. 99 BOGOR',
            'AlamatPT2' => 'Jawa Barat',
            'TelponPT' => '0251-8212225-26',
            'WebsitePT' => 'www.theforestresorthotel.com',
            'EmailPT' => 'info@theforesthotel.com',
            'LogoPT' => null,
            'FormTheme' => 'ocean-blue',
            'BrandingUpdatedAt' => null,
        ];
    }

    public static function profile(): array
    {
        $profile = array_merge(static::defaults(), static::loadSetupProfile());

        if (empty($profile['LogoPT'])) {
            $legacyLogo = static::loadLegacyLogoPath();
            if ($legacyLogo !== null) {
                $profile['LogoPT'] = $legacyLogo;
            }
        }

        return static::withAliases($profile);
    }

    public static function save(array $attributes, ?UploadedFile $logo = null, bool $removeLogo = false): array
    {
        $profile = static::profile();

        foreach (['NamaPT', 'UsahaPT', 'FaxPT', 'AlamatPT', 'AlamatPT2', 'TelponPT', 'WebsitePT', 'EmailPT'] as $field) {
            $profile[$field] = trim((string) ($attributes[$field] ?? $profile[$field] ?? ''));
        }

        $profile['FormTheme'] = static::normalizeThemeKey((string) ($attributes['FormTheme'] ?? ($profile['FormTheme'] ?? 'ocean-blue')));

        if ($removeLogo && !empty($profile['LogoPT'])) {
            static::deleteLogoFile($profile['LogoPT']);
            $profile['LogoPT'] = null;
        }

        if ($logo instanceof UploadedFile) {
            if (!empty($profile['LogoPT'])) {
                static::deleteLogoFile($profile['LogoPT']);
            }

            $extension = strtolower($logo->getClientOriginalExtension() ?: $logo->extension() ?: 'png');
            $filename = 'hotel-logo-' . Str::uuid() . '.' . $extension;
            $relativePath = 'public/hotel-branding/' . $filename;
            $destination = storage_path('app/' . dirname($relativePath));

            File::ensureDirectoryExists($destination);
            $logo->move($destination, basename($relativePath));

            $profile['LogoPT'] = $relativePath;
        }

        $profile['BrandingUpdatedAt'] = now()->toDateTimeString();

        static::persistSetupProfile($profile);
        static::persistLegacyLogoPath($profile['LogoPT']);

        return static::withAliases($profile);
    }

    public static function logoAbsolutePath(?array $profile = null): ?string
    {
        $profile = $profile ?: static::profile();
        $relativePath = trim((string) ($profile['LogoPT'] ?? $profile['logo_path'] ?? ''));

        if ($relativePath === '') {
            return null;
        }

        $absolutePath = storage_path('app/' . ltrim($relativePath, '/'));

        return File::exists($absolutePath) ? $absolutePath : null;
    }

    private static function loadSetupProfile(): array
    {
        $row = DB::table('setup')
            ->whereRaw('RTRIM(Kode) = ?', ['01'])
            ->first();

        if (!$row) {
            return [];
        }

        return [
            'NamaPT' => trim((string) ($row->NamaPT ?? '')),
            'UsahaPT' => trim((string) ($row->UsahaPT ?? '')),
            'FaxPT' => trim((string) ($row->FaxPT ?? '')),
            'AlamatPT' => trim((string) ($row->AlamatPT ?? '')),
            'AlamatPT2' => trim((string) ($row->AlamatPT2 ?? '')),
            'TelponPT' => static::normalizePhone((string) ($row->TelponPT ?? '')),
            'WebsitePT' => trim((string) ($row->WebsitePT ?? '')),
            'EmailPT' => trim((string) ($row->EmailPT ?? '')),
            'LogoPT' => static::resolveLogoColumn($row),
            'FormTheme' => static::resolveThemeColumn($row),
            'BrandingUpdatedAt' => property_exists($row, 'BrandingUpdatedAt') ? trim((string) ($row->BrandingUpdatedAt ?? '')) : null,
        ];
    }

    private static function persistSetupProfile(array $profile): void
    {
        $payload = [
            'NamaPT' => $profile['NamaPT'],
            'UsahaPT' => $profile['UsahaPT'],
            'FaxPT' => $profile['FaxPT'],
            'AlamatPT' => $profile['AlamatPT'],
            'AlamatPT2' => $profile['AlamatPT2'],
            'TelponPT' => $profile['TelponPT'],
            'WebsitePT' => $profile['WebsitePT'],
            'EmailPT' => $profile['EmailPT'],
            'NAMAHOTEL' => $profile['NamaPT'],
            'ALAMATHOTEL' => $profile['AlamatPT'],
            'TELPONHOTEL' => 'Telp : ' . $profile['TelponPT'],
        ];

        if (Schema::hasColumn('setup', 'LogoPT')) {
            $payload['LogoPT'] = $profile['LogoPT'];
        }

        if (Schema::hasColumn('setup', 'BrandingUpdatedAt')) {
            $payload['BrandingUpdatedAt'] = $profile['BrandingUpdatedAt'];
        }

        if (Schema::hasColumn('setup', 'FormTheme')) {
            $payload['FormTheme'] = $profile['FormTheme'];
        }

        DB::table('setup')
            ->whereRaw('RTRIM(Kode) = ?', ['01'])
            ->update($payload);
    }

    private static function resolveLogoColumn(object $row): ?string
    {
        if (property_exists($row, 'LogoPT')) {
            $value = trim((string) ($row->LogoPT ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private static function resolveThemeColumn(object $row): string
    {
        if (property_exists($row, 'FormTheme')) {
            return static::normalizeThemeKey((string) ($row->FormTheme ?? ''));
        }

        return 'ocean-blue';
    }

    private static function withAliases(array $profile): array
    {
        $profile['name'] = $profile['NamaPT'] ?? '';
        $profile['business'] = $profile['UsahaPT'] ?? '';
        $profile['fax'] = $profile['FaxPT'] ?? '';
        $profile['address'] = $profile['AlamatPT'] ?? '';
        $profile['address_secondary'] = $profile['AlamatPT2'] ?? '';
        $profile['phone'] = $profile['TelponPT'] ?? '';
        $profile['website'] = $profile['WebsitePT'] ?? '';
        $profile['email'] = $profile['EmailPT'] ?? '';
        $profile['logo_path'] = $profile['LogoPT'] ?? null;
        $profile['form_theme'] = static::normalizeThemeKey((string) ($profile['FormTheme'] ?? 'ocean-blue'));
        $profile['updated_at'] = $profile['BrandingUpdatedAt'] ?? null;

        return $profile;
    }

    public static function themeOptions(): array
    {
        return [
            'ocean-blue' => [
                'label' => 'Ocean Blue',
                'description' => 'Clean blue-and-white styling with a crisp front office feel.',
                'swatches' => ['#eef5ff', '#173761', '#ffffff', '#dbe8ff'],
            ],
            'soft-gold' => [
                'label' => 'Soft Gold',
                'description' => 'Warm soft-gold tones with a gentle premium hospitality mood.',
                'swatches' => ['#fff7df', '#8a6424', '#fffdf7', '#f3df9b'],
            ],
            'forest-sage' => [
                'label' => 'Forest Sage',
                'description' => 'Calm sage green styling that fits a resort-like atmosphere.',
                'swatches' => ['#edf6ef', '#1f5c45', '#ffffff', '#cfe4d7'],
            ],
            'rose-champagne' => [
                'label' => 'Rose Champagne',
                'description' => 'Soft champagne blush with a polished boutique hotel tone.',
                'swatches' => ['#fff6f4', '#8b5c63', '#ffffff', '#f2d7d6'],
            ],
            'stone-navy' => [
                'label' => 'Stone Navy',
                'description' => 'Muted stone neutrals with deep navy accents for a classic look.',
                'swatches' => ['#f3f5f7', '#203a57', '#ffffff', '#d6dee6'],
            ],
        ];
    }

    public static function themeVariables(?array $profile = null): array
    {
        $profile = $profile ?: static::profile();
        $themeKey = static::normalizeThemeKey((string) ($profile['FormTheme'] ?? $profile['form_theme'] ?? 'ocean-blue'));

        $themes = [
            'ocean-blue' => [
                'page_bg' => 'radial-gradient(circle at top right, rgba(30, 75, 128, 0.08), transparent 22%), radial-gradient(circle at left top, rgba(16, 35, 59, 0.08), transparent 28%), linear-gradient(180deg, #f0f4f8 0%, #eef1f6 45%, #e7edf5 100%)',
                'shell_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.96))',
                'shell_border' => 'rgba(30, 75, 128, 0.18)',
                'shell_shadow' => '0 24px 60px rgba(30, 75, 128, 0.12)',
                'header_bg' => 'linear-gradient(180deg, rgba(238, 241, 246, 0.6), rgba(255, 255, 255, 0.8))',
                'heading_bg' => 'linear-gradient(180deg, rgba(238, 241, 246, 0.5), rgba(255, 255, 255, 0.9))',
                'heading_border' => 'rgba(30, 75, 128, 0.15)',
                'title' => '#173761',
                'text' => '#10233b',
                'muted' => '#6b7b90',
                'label' => '#233f6b',
                'badge_bg' => 'rgba(30, 75, 128, 0.14)',
                'badge_text' => '#173761',
                'input_bg' => 'rgba(255, 255, 255, 0.95)',
                'input_border' => 'rgba(30, 75, 128, 0.18)',
                'input_focus' => 'rgba(30, 75, 128, 0.78)',
                'input_shadow' => '0 0 0 0.2rem rgba(30, 75, 128, 0.12)',
                'button_primary' => 'linear-gradient(135deg, #173761 0%, #1e4b80 100%)',
                'button_secondary_bg' => 'rgba(255, 255, 255, 0.9)',
                'button_secondary_text' => '#173761',
                'table_head_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(238, 241, 246, 0.78))',
                'table_odd' => 'rgba(16, 35, 59, 0.045)',
                'table_even' => 'rgba(255, 255, 255, 0.96)',
                'table_hover' => 'rgba(30, 75, 128, 0.06)',
                'table_hover_accent' => '#1e4b80',
            ],
            'soft-gold' => [
                'page_bg' => 'radial-gradient(circle at top right, rgba(199, 160, 60, 0.08), transparent 22%), radial-gradient(circle at left top, rgba(142, 108, 33, 0.08), transparent 28%), linear-gradient(180deg, #fffdf4 0%, #fff8eb 48%, #f6efe2 100%)',
                'shell_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(255, 252, 245, 0.98))',
                'shell_border' => 'rgba(181, 145, 60, 0.22)',
                'shell_shadow' => '0 24px 60px rgba(169, 129, 33, 0.11)',
                'header_bg' => 'linear-gradient(180deg, rgba(255, 246, 220, 0.85), rgba(255, 255, 255, 0.88))',
                'heading_bg' => 'linear-gradient(180deg, rgba(255, 247, 225, 0.88), rgba(255, 255, 255, 0.96))',
                'heading_border' => 'rgba(190, 153, 70, 0.18)',
                'title' => '#7a5617',
                'text' => '#43321b',
                'muted' => '#8d7858',
                'label' => '#8a6424',
                'badge_bg' => 'rgba(190, 153, 70, 0.16)',
                'badge_text' => '#7a5617',
                'input_bg' => 'rgba(255, 253, 247, 0.98)',
                'input_border' => 'rgba(190, 153, 70, 0.22)',
                'input_focus' => 'rgba(190, 153, 70, 0.78)',
                'input_shadow' => '0 0 0 0.2rem rgba(190, 153, 70, 0.14)',
                'button_primary' => 'linear-gradient(135deg, #9f7427 0%, #c49135 100%)',
                'button_secondary_bg' => 'rgba(255, 252, 244, 0.96)',
                'button_secondary_text' => '#835b16',
                'table_head_bg' => 'linear-gradient(180deg, rgba(255, 252, 244, 0.96), rgba(255, 245, 220, 0.85))',
                'table_odd' => 'rgba(179, 137, 42, 0.06)',
                'table_even' => 'rgba(255, 255, 255, 0.98)',
                'table_hover' => 'rgba(190, 153, 70, 0.09)',
                'table_hover_accent' => '#b7852e',
            ],
            'forest-sage' => [
                'page_bg' => 'radial-gradient(circle at top right, rgba(52, 110, 84, 0.08), transparent 22%), radial-gradient(circle at left top, rgba(31, 92, 69, 0.08), transparent 28%), linear-gradient(180deg, #f3f8f4 0%, #eef5f0 45%, #e6efe8 100%)',
                'shell_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 253, 251, 0.97))',
                'shell_border' => 'rgba(52, 110, 84, 0.18)',
                'shell_shadow' => '0 24px 60px rgba(52, 110, 84, 0.11)',
                'header_bg' => 'linear-gradient(180deg, rgba(231, 241, 234, 0.82), rgba(255, 255, 255, 0.88))',
                'heading_bg' => 'linear-gradient(180deg, rgba(236, 246, 239, 0.82), rgba(255, 255, 255, 0.96))',
                'heading_border' => 'rgba(52, 110, 84, 0.16)',
                'title' => '#1f5c45',
                'text' => '#173126',
                'muted' => '#667e72',
                'label' => '#2e614e',
                'badge_bg' => 'rgba(52, 110, 84, 0.15)',
                'badge_text' => '#1f5c45',
                'input_bg' => 'rgba(255, 255, 255, 0.96)',
                'input_border' => 'rgba(52, 110, 84, 0.18)',
                'input_focus' => 'rgba(52, 110, 84, 0.74)',
                'input_shadow' => '0 0 0 0.2rem rgba(52, 110, 84, 0.12)',
                'button_primary' => 'linear-gradient(135deg, #1f5c45 0%, #347555 100%)',
                'button_secondary_bg' => 'rgba(252, 255, 253, 0.96)',
                'button_secondary_text' => '#1f5c45',
                'table_head_bg' => 'linear-gradient(180deg, rgba(253, 255, 254, 0.96), rgba(233, 242, 236, 0.84))',
                'table_odd' => 'rgba(31, 92, 69, 0.045)',
                'table_even' => 'rgba(255, 255, 255, 0.97)',
                'table_hover' => 'rgba(52, 110, 84, 0.08)',
                'table_hover_accent' => '#347555',
            ],
            'rose-champagne' => [
                'page_bg' => 'radial-gradient(circle at top right, rgba(173, 118, 126, 0.08), transparent 22%), radial-gradient(circle at left top, rgba(139, 92, 99, 0.08), transparent 28%), linear-gradient(180deg, #fff9f8 0%, #fdf2f1 45%, #f5e8e7 100%)',
                'shell_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.985), rgba(255, 250, 249, 0.975))',
                'shell_border' => 'rgba(173, 118, 126, 0.18)',
                'shell_shadow' => '0 24px 60px rgba(173, 118, 126, 0.11)',
                'header_bg' => 'linear-gradient(180deg, rgba(252, 236, 236, 0.86), rgba(255, 255, 255, 0.9))',
                'heading_bg' => 'linear-gradient(180deg, rgba(255, 240, 240, 0.88), rgba(255, 255, 255, 0.97))',
                'heading_border' => 'rgba(173, 118, 126, 0.16)',
                'title' => '#8b5c63',
                'text' => '#412b30',
                'muted' => '#8d7278',
                'label' => '#9a646d',
                'badge_bg' => 'rgba(173, 118, 126, 0.15)',
                'badge_text' => '#8b5c63',
                'input_bg' => 'rgba(255, 255, 255, 0.97)',
                'input_border' => 'rgba(173, 118, 126, 0.18)',
                'input_focus' => 'rgba(173, 118, 126, 0.76)',
                'input_shadow' => '0 0 0 0.2rem rgba(173, 118, 126, 0.12)',
                'button_primary' => 'linear-gradient(135deg, #8b5c63 0%, #b1737d 100%)',
                'button_secondary_bg' => 'rgba(255, 252, 252, 0.96)',
                'button_secondary_text' => '#8b5c63',
                'table_head_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(249, 233, 233, 0.86))',
                'table_odd' => 'rgba(139, 92, 99, 0.04)',
                'table_even' => 'rgba(255, 255, 255, 0.975)',
                'table_hover' => 'rgba(173, 118, 126, 0.08)',
                'table_hover_accent' => '#b1737d',
            ],
            'stone-navy' => [
                'page_bg' => 'radial-gradient(circle at top right, rgba(54, 82, 108, 0.08), transparent 22%), radial-gradient(circle at left top, rgba(32, 58, 87, 0.08), transparent 28%), linear-gradient(180deg, #f6f7f8 0%, #eef1f3 45%, #e7eaee 100%)',
                'shell_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.985), rgba(249, 250, 251, 0.975))',
                'shell_border' => 'rgba(54, 82, 108, 0.17)',
                'shell_shadow' => '0 24px 60px rgba(32, 58, 87, 0.11)',
                'header_bg' => 'linear-gradient(180deg, rgba(236, 240, 244, 0.9), rgba(255, 255, 255, 0.9))',
                'heading_bg' => 'linear-gradient(180deg, rgba(242, 245, 247, 0.92), rgba(255, 255, 255, 0.97))',
                'heading_border' => 'rgba(54, 82, 108, 0.15)',
                'title' => '#203a57',
                'text' => '#233240',
                'muted' => '#708090',
                'label' => '#39526c',
                'badge_bg' => 'rgba(54, 82, 108, 0.14)',
                'badge_text' => '#203a57',
                'input_bg' => 'rgba(255, 255, 255, 0.97)',
                'input_border' => 'rgba(54, 82, 108, 0.17)',
                'input_focus' => 'rgba(54, 82, 108, 0.74)',
                'input_shadow' => '0 0 0 0.2rem rgba(54, 82, 108, 0.12)',
                'button_primary' => 'linear-gradient(135deg, #203a57 0%, #36526c 100%)',
                'button_secondary_bg' => 'rgba(252, 253, 254, 0.96)',
                'button_secondary_text' => '#203a57',
                'table_head_bg' => 'linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(236, 240, 244, 0.9))',
                'table_odd' => 'rgba(32, 58, 87, 0.038)',
                'table_even' => 'rgba(255, 255, 255, 0.975)',
                'table_hover' => 'rgba(54, 82, 108, 0.08)',
                'table_hover_accent' => '#36526c',
            ],
        ];

        return $themes[$themeKey] ?? $themes['ocean-blue'];
    }

    private static function normalizeThemeKey(string $theme): string
    {
        $theme = strtolower(trim($theme));

        return array_key_exists($theme, static::themeOptions()) ? $theme : 'ocean-blue';
    }

    private static function normalizePhone(string $phone): string
    {
        $trimmed = trim($phone);

        return preg_replace('/^Telp\s*:\s*/i', '', $trimmed) ?: $trimmed;
    }

    private static function legacySettingsPath(): string
    {
        return storage_path('app/hotel-branding.json');
    }

    private static function loadLegacyLogoPath(): ?string
    {
        $path = static::legacySettingsPath();

        if (!File::exists($path)) {
            return null;
        }

        $decoded = json_decode((string) File::get($path), true);

        if (!is_array($decoded)) {
            return null;
        }

        $value = trim((string) ($decoded['logo_path'] ?? ''));

        return $value !== '' ? $value : null;
    }

    private static function persistLegacyLogoPath(?string $logoPath): void
    {
        File::ensureDirectoryExists(dirname(static::legacySettingsPath()));
        File::put(static::legacySettingsPath(), json_encode([
            'logo_path' => $logoPath,
            'updated_at' => now()->toDateTimeString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private static function deleteLogoFile(string $relativePath): void
    {
        $absolutePath = storage_path('app/' . ltrim($relativePath, '/'));

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }
}
