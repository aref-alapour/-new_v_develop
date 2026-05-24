# Flatten Tailwind arbitrary [...px] / common shadow/grid/flex patterns into @theme tokens.
# Spacing-based: d{px} or d18p5 for decimals -> --spacing-d* (see tailwind-dimensions-from-sources.css)
$ErrorActionPreference = 'Stop'
$themeRoot = Split-Path -Parent $PSScriptRoot
$snippetPath = Join-Path $themeRoot 'assets/css/partials/tailwind-dimensions-from-sources.css'
$themeCssPath = Join-Path $themeRoot 'assets/css/tailwind-theme.css'
$hexToToken = [System.Collections.Generic.Dictionary[string,string]]::new([StringComparer]::Ordinal)
foreach ($line in [IO.File]::ReadLines($themeCssPath)) {
  if ($line -match '^\s*--color-([a-zA-Z0-9-]+)\s*:\s*(#[0-9a-fA-F]{6})\s*;') {
    $hx = $Matches[2].ToLowerInvariant()
    if (-not $hexToToken.ContainsKey($hx)) { $hexToToken[$hx] = $Matches[1] }
  }
}

function Get-PxToken([string]$v) {
  return 'd' + ($v -replace '\.', 'p')
}

function Parse-PxFromDTok([string]$tok) {
  if ($tok -match '^(\d+)p(\d+)$') {
    return "$($Matches[1]).$($Matches[2])"
  }
  return $tok
}

$files = Get-ChildItem -Path $themeRoot -Recurse -Include *.php, *.js, *.html, *.tsx -File |
  Where-Object { $_.FullName -notmatch '\\node_modules\\|\\vendor\\|\\dist\\|highlight\.min\.js' }

# After w-[176px] -> w-d176, px no longer appears as [...px]; scan existing -d* utilities too.
$dFromPropRe = [regex]'(?<![\w-])(?:[a-z0-9_-]+:)*(?:!)?(?:min-w|max-w|min-h|max-h|w|h|gap-x|gap-y|gap|px|py|pt|pb|pl|pr|p|m|mt|mb|ml|mr|my|mx|top|bottom|left|right)-d(?<tok>\d+(?:p\d+)?)(?![\w-])'
$dNegPosMarginRe = [regex]'(?<![\w-])(?:[a-z0-9_-]+:)*-(?:top|bottom|left|right|ml|mr|my|mx)-d(?<tok>\d+(?:p\d+)?)(?![\w-])'

$dimRe = [regex]'(?<![\w-])(?:[\w:-]+:)*(?<prop>min-w|max-w|min-h|max-h|w|h|gap|gap-x|gap-y|p|px|py|pt|pb|pl|pr|m|mt|mb|ml|mr|my|mx|top|bottom|left|right)-\[(?<px>\d+(?:\.\d+)?)px\]'
$pxSet = [System.Collections.Generic.HashSet[string]]::new()

foreach ($f in $files) {
  $t = [IO.File]::ReadAllText($f.FullName)
  foreach ($m in $dimRe.Matches($t)) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in [regex]::Matches($t, '(?<![\w-])(?:[\w:-]+:)*text-\[(?<px>\d+)px\]')) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in [regex]::Matches($t, '(?<![\w-])(?:[\w:-]+:)*rounded-\[(?<px>\d+)px\]')) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in [regex]::Matches($t, '(?<![\w-])(?:[\w:-]+:)*leading-\[(?<px>\d+)px\]')) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in [regex]::Matches($t, '(?<![\w-])(?:[\w:-]+:)*(?:top|left|right|bottom)-\[\-(?<px>\d+)px\]')) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in [regex]::Matches($t, '(?<![\w-])(?:[\w:-]+:)*mr-\[\-(?<px>\d+)px\]')) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in [regex]::Matches($t, '(?<![\w-])(?:[\w:-]+:)*ml-\[\-(?<px>\d+)px\]')) { [void]$pxSet.Add($m.Groups['px'].Value) }
  foreach ($m in $dFromPropRe.Matches($t)) { [void]$pxSet.Add((Parse-PxFromDTok $m.Groups['tok'].Value)) }
  foreach ($m in $dNegPosMarginRe.Matches($t)) { [void]$pxSet.Add((Parse-PxFromDTok $m.Groups['tok'].Value)) }
}

$orderedPx = $pxSet | Sort-Object { [double]$_ }
$themeLines = @(
  '/* AUTO-GENERATED: scripts/normalize-tailwind-arbitrary.ps1 — do not edit by hand */',
  '@theme {'
)
foreach ($px in $orderedPx) {
  $tok = Get-PxToken $px
  $themeLines += "  --spacing-$tok`: ${px}px;"
}
$themeLines += '}', ''
[IO.File]::WriteAllText($snippetPath, ($themeLines -join "`n"), [Text.UTF8Encoding]::new($false))
Write-Host "Dimensions: $($pxSet.Count) tokens -> $snippetPath"

$utf8 = [Text.UTF8Encoding]::new($false)
foreach ($f in $files) {
  $orig = [IO.File]::ReadAllText($f.FullName)
  $s = $orig

  $s = $s -replace 'flex-\[0_0_100%\]', 'shrink-0 grow-0 basis-full'
  $s = $s -replace 'flex-\[0_0_\d+%\]', 'shrink-0 grow-0 basis-full'
  $s = $s -replace 'rounded-\[50%\]', 'rounded-full'
  $s = $s -replace 'after:rounded-\[50%\]', 'after:rounded-full'

  $s = $s -replace '(?<![\w-])-ml-\[50vw\]', '-ml-50vw'
  $s = $s -replace '(?<![\w-])-mr-\[50vw\]', '-mr-50vw'
  $s = $s -replace 'max-lg:shadow-\[inset_0_4px_4px_rgba\(0,0,0,0\.25\)\]', 'max-lg:shadow-inset-hero'
  $s = $s -replace 'shadow-\[0px_4px_4px_0px_rgba\(0_0_0_0\.25\)\]', 'shadow-card-float'
  $s = $s -replace 'shadow-\[0px_4px_4px_0px_rgba\(0,0,0,0\.25\)\]', 'shadow-card-float'
  $s = $s -replace 'shadow-\[0px_-10px_10px_0px_rgba\(0,0,0,0\.10\)\]', 'shadow-sheet-up'
  $s = $s -replace 'shadow-\[0px_-10px_10px_0px_rgba\(0,0,0,0\.1\)\]', 'shadow-sheet-up'
  $s = $s -replace 'lg:grid-cols-\[1fr_2fr_2fr_2fr_3fr_1\.5fr\]', 'lg:grid-cols-footer-cities'
  $s = $s -replace 'duration-\[2000ms\]', 'duration-2000'
  $s = $s -replace '(?<![\w-])translate-y-\[-115px\]', '-translate-y-115'
  $s = $s -replace '(?<![\w-])z-\[3\]', 'z-3'
  $s = $s -replace '(?<![\w-])z-\[4\]', 'z-4'
  $s = $s -replace '(?<![\w-])z-\[-1\]', '-z-1'
  $s = $s -replace '(?<![\w-])z-\[-10\]', '-z-10'

  $s = [regex]::Replace($s, '(?<![\w-])(?<p>(?:[a-z0-9_-]+:)+)\[word-spacing:0\.375rem\]', { param($mm) $mm.Groups['p'].Value + 'word-spacing-nav' })
  $s = $s -replace '(?<![\w-])\[word-spacing:0\.375rem\]', 'word-spacing-nav'
  $s = $s -replace 'top-\[1px\]', 'top-px'
  $s = $s -replace 'py-\[4px\]', 'py-1'
  $s = $s -creplace 'mx-w-\[', 'max-w-['

  $reHexColor = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)(?<prop>text|bg|border(?:-[trblxy])?|ring|outline|fill|stroke|from|via|to|decoration)-\[(?<hex>#[\da-fA-F]{6})\](?<op>/\d+)?'
  $s = $reHexColor.Replace($s, {
      param($mmArg)
      $hx = $mmArg.Groups['hex'].Value.ToLowerInvariant()
      if (-not $hexToToken.ContainsKey($hx)) { return $mmArg.Value }
      $op = $mmArg.Groups['op'].Value
      $mmArg.Groups['prefix'].Value + $mmArg.Groups['prop'].Value + '-' + $hexToToken[$hx] + $op
    })

  $reRounded = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)rounded-\[(\d+)px\]'
  $s = $reRounded.Replace($s, '${prefix}rounded-$2')
  $reText = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)text-\[(\d+)px\]'
  $s = $reText.Replace($s, '${prefix}text-$2')
  $reLead = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)leading-\[(\d+)px\]'
  $s = $reLead.Replace($s, '${prefix}leading-$2')

  $reDim = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)(min-w|max-w|min-h|max-h|w|h|gap|gap-x|gap-y|p|px|py|pt|pb|pl|pr|m|mt|mb|ml|mr|my|mx)-\[(\d+(?:\.\d+)?)px\]'
  $s = $reDim.Replace($s, {
      param($mm)
      $tok = Get-PxToken $mm.Groups[3].Value
      $mm.Groups[1].Value + $mm.Groups[2].Value + '-' + $tok
    })

  $s = $s -replace 'max-sm:max-w-\[calc\(100%\+2rem\)\]', 'max-sm:max-w-bleed-2'
  $s = $s -replace 'max-sm:w-\[calc\(100%\+2rem\)\]', 'max-sm:w-bleed-2'
  $s = $s -replace 'z-\[9999\]', 'z-9999'
  $s = $s -replace 'max-h-\[90vh\]', 'max-h-modal'
  $s = $s -replace 'shadow-\[0_-4px_10px_rgba\(0,0,0,0\.1\)\]', 'shadow-modal-edge'
  $s = $s -replace '\!leading-\[1\.7\]', '!leading-17'
  $s = $s -replace 'max-lg:ml-\[-50vw\]', 'max-lg:-ml-50vw'
  $s = $s -replace 'max-lg:mr-\[-50vw\]', 'max-lg:-mr-50vw'
  $s = $s -replace 'h-\[100%\]', 'h-full'
  $s = $s -replace 'lg:border-\[1px\]', 'lg:border'

  $s = [regex]::Replace($s, 'rounded-(t|r|b|l|tl|tr|bl|br)-\[(\d+)px\]', {
    param($mm)
    'rounded-' + $mm.Groups[1].Value + '-' + $mm.Groups[2].Value
  })

  $reMr = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)(mr|ml)-\[-(\d+)px\]'
  $s = $reMr.Replace($s, '${prefix}-$2-d$3')

  $rePos = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)(top|left|right|bottom|my|mx)-\[(\d+)px\]'
  $s = $rePos.Replace($s, '${prefix}$2-d$3')

  $rePosNeg = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)(top|left|right|bottom|my|mx)-\[-(\d+)px\]'
  $s = $rePosNeg.Replace($s, '${prefix}-$2-d$3')

  $reZ = [regex]'(?<![\w-])(?<prefix>(?:[a-z0-9_-]+:)*)z-\[-(\d+)\]'
  $s = $reZ.Replace($s, '${prefix}-z-$2')

  if ($s -ne $orig) {
    [IO.File]::WriteAllText($f.FullName, $s, $utf8)
    Write-Host $f.FullName
  }
}
