# جایگزینی کلاس‌های arbitrary رنگ [ #... ] با توکن‌های @theme (Tailwind v4).
$ErrorActionPreference = 'Stop'
$themeRoot = Split-Path -Parent $PSScriptRoot

# hex بدون # — مقدار = نام رنگ در تم (فقط نام، بدون پیشوند bg/text)
$six = @{
  '0041ac' = 'blue'; '0084ff' = 'info'; '00a32a' = 'wp-green'; '00a349' = 'tgreen-hover'
  '00b2ff' = 'info'; '00b350' = 'tgreen-1'
  '01a75c' = 'accent-700'; '01b05a' = 'accent-700'; '01b061' = 'success-lip'
  '02a159' = 'accent-700'; '02c96f' = 'accent-450'; '049654' = 'accent-950'
  '04b968' = 'accent-550'; '059669' = 'health-green'; '09192d' = 'slate-700'
  '0f172a' = 'slate-900'; '0f172b' = 'ink-tab'; '0f5a8a' = 'slate-800'
  '10b981' = 'health-green'; '137cb1' = 'blue'; '1447e6' = 'blue-link'
  '155dfc' = 'focus-blue'; '158eff' = 'ring-focus'; '16a34a' = 'health-green'
  '1c0000' = 'black'; '1c398e' = 'slate-900'; '1e293b' = 'slate-800'
  '1e40af' = 'focus-blue-deep'; '1e5bb8' = 'blue'; '1e5fbf' = 'blue'
  '1ed982' = 'payment-title'; '252728' = 'slate-800'; '2563eb' = 'blue'
  '294487' = 'slate-800'; '2b7fff' = 'focus-blue'; '344054' = 'slate-260'
  '3b82f6' = 'blue'; '3f7ff5' = 'blue'; '4e5c6d' = 'slate-250'
  '5091fb' = 'blue'; '535e6c' = 'divider-charcoal'; '565656' = 'text-charcoal'
  '5e6b77' = 'slate-250'; '62748e' = 'steel'; '64748b' = 'slate-ui'
  '69737f' = 'body-soft'; '6d28d9' = 'secondary-700'; '733e0a' = 'yellow-900'
  '7c3aed' = 'secondary-600'; '7e7e7e' = 'gray-600'; '858585' = 'gray-600'
  '888888' = 'neutral-888'; '889bad' = 'text-3'; '894b00' = 'yellow-900'
  '90a1b9' = 'muted-blue'; '91a0a7' = 'gray-ui'; '9aa8b7' = 'slate-200'
  'a7a7a7' = 'gray-600'; 'aaaaaa' = 'text-2'; 'bf9a00' = 'yellow-900'
  'c2410c' = 'primary-900'; 'c29d04' = 'yellow-800'; 'c4c4c4' = 'gray-200'
  'ca5608' = 'primary-deep'; 'cad5e2' = 'slate-120'; 'cbd5e1' = 'slate-130'
  'cbdefe' = 'blue'; 'ccc' = 'disabled'; 'd08700' = 'amber-ring'
  'd0deec' = 'slate-110'; 'd5dce1' = 'slate-130'; 'd75602' = 'promo-3'
  'd8d8d8' = 'gray-200'; 'dadada' = 'border-1'; 'dbcab3' = 'yellow-300'
  'dbe2ea' = 'rail'; 'dc2626' = 'error'; 'e1f9ee' = 'accent-20'
  'e2e8f0' = 'slate-110'; 'e4ebf0' = 'slate-105'; 'e55a0a' = 'primary-600'
  'e6f4ee' = 'accent-50'; 'e6faf1' = 'accent-20'; 'e8edf1' = 'edge'
  'ebe6db' = 'gray-100'; 'ececee' = 'slate-110'; 'ecf2f7' = 'gray-100'
  'eda10d' = 'yellow-500'; 'efc101' = 'yellow-500'; 'ee6003' = 'primary-500'; 'eee8e8' = 'secondary-100'
  'eef1f5' = 'slate-100'; 'ef4444' = 'error'; 'ef4e5d' = 'secondary-500'
  'f1f5f9' = 'surface-sunken'; 'f21543' = 'danger-hot'; 'f2f6fa' = 'gray-50'
  'f3f4f6' = 'surface-pill'; 'f3f9fc' = 'gray-50'; 'f4f9fc' = 'gray-50'
  'f7f4ed' = 'warn-surface-2'; 'f7fafa' = 'gray-20'; 'f8fafb' = 'gray-20'
  'f8fafc' = 'surface-muted'; 'f9f9f9' = 'gray-20'; 'f9fafb' = 'breserve'
  'fafafa' = 'gray-20'; 'fafdff' = 'white'; 'fcf6d1' = 'warn-surface-2'
  'fd6a2e' = 'promo-2'; 'fd7013' = 'primary-2'; 'fdf7de' = 'warn-surface'
  'fdf9e6' = 'warn-surface-3'; 'feae1a' = 'yellow-400'; 'fed4b8' = 'primary-100'
  'fee8ec' = 'secondary-100'; 'ff1800' = 'error'; 'ff6900' = 'primary-500'
  'ff6b47' = 'promo-1'; 'ff7a00' = 'primary-500'; 'ffedd4' = 'primary-100'
  'fff1e9' = 'primary-100'; 'fff4ed' = 'primary-100'; 'fff7ed' = 'yellow-100'
  'ffffff' = 'white'
  '5d6a77' = 'review-muted'; '164179' = 'navy-banner'
}
# ۸ رقمی = نام + شفافیت Tailwind
$eight = @{
  'f215431a' = 'danger-hot/10'; 'fd70131a' = 'primary-2/10'
  'efc1011a' = 'yellow-500/10'; '5091fb1a' = 'blue/10'
  'fd701338' = 'primary-2/20'; 'feae1a66' = 'yellow-400/40'
}

$shadowPairs = @(
  @{ O = 'shadow-[0px_2px_0px_0px_#E8EDF1]'; N = 'shadow-card-lip' }
  @{ O = 'shadow-[0px_1px_0px_0px_#DBE2EA]'; N = 'shadow-rail-lip' }
  @{ O = 'shadow-[0_2px_0_0_#CA5608]'; N = 'shadow-primary-lip' }
  @{ O = 'shadow-[0_2px_0_#01B061]'; N = 'shadow-success-lip' }
  @{ O = 'shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)]'; N = 'shadow-dropdown' }
  @{ O = 'shadow-[0_2px_4px_0_rgba(9,25,45,0.13)]'; N = 'shadow-mobile-nav' }
  @{ O = 'shadow-[0_2px_2px_1px_#00000042]'; N = 'shadow-wp-button' }
  @{ O = 'shadow-[0_2px_10px_rgba(15,23,42,0.08)]'; N = 'shadow-comment-card' }
  @{ O = 'max-lg:shadow-[0px_2px_0px_0px_#e2e8f0]'; N = 'max-lg:shadow-slate-lip' }
)

$re = [regex]::new(
  '(?<mod>(?:[a-z][a-z0-9-]*:)*)(?<pfx>bg|text|border|border-t|border-r|border-b|border-l|ring|divide|from|to|via|outline|placeholder|decoration|fill|stroke|caret)-\[(?<hx>#[0-9a-fA-F]{3,8}|#ccc)\](?<op>/[0-9]+)?',
  [System.Text.RegularExpressions.RegexOptions]::IgnoreCase
)

$files = Get-ChildItem -Path $themeRoot -Recurse -Include *.php, *.js, *.html |
  Where-Object { $_.FullName -notmatch '\\dist\\|\\node_modules\\|\\vendor\\|\\scripts\\' }

foreach ($f in $files) {
  $t = [IO.File]::ReadAllText($f.FullName)
  $orig = $t
  foreach ($sp in $shadowPairs) {
    $t = $t.Replace($sp.O, $sp.N)
  }
  $t = $re.Replace($t, {
      param($m)
      $hx = $m.Groups['hx'].Value.TrimStart('#').ToLower()
      $mod = $m.Groups['mod'].Value
      $pfx = $m.Groups['pfx'].Value
      $op = $m.Groups['op'].Value
      if ($eight.ContainsKey($hx)) {
        return $mod + $pfx + '-' + $eight[$hx]
      }
      if (-not $six.ContainsKey($hx)) { return $m.Value }
      $name = $six[$hx]
      return $mod + $pfx + '-' + $name + $op
    })
  if ($t -ne $orig) {
    [IO.File]::WriteAllText($f.FullName, $t, [Text.UTF8Encoding]::new($false))
    Write-Host $f.FullName
  }
}
