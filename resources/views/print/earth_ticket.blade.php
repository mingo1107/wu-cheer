<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="utf-8" />
<title>土單列印</title>
<style id="print-style">
  body { margin:0; padding:0; background:#FAFAFA; font-family:'Courier New','Consolas','Lucida Console',monospace; }
  .page { padding: 5mm 3mm 0 2mm; page-break-after: always; }
  .sheet { width: 200mm; margin: 0 auto; }
  table { width: 200mm; table-layout: fixed; border-collapse: collapse; font-size: 14px; }
  th,td { border:1px solid #000; padding:2px 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .no-border { border:none !important; }
  .title { font-size: 18px; font-weight: bold; text-align: center; }
  .sub { font-size: 12px; color:#444; }
  .block { margin-bottom: 3px; }

  @media print {
    @page { margin: 0mm; size: 215mm 145mm; }
    html,body { -webkit-print-color-adjust: exact; }
    .page { margin:0; border: initial; width: initial; min-height: initial; box-shadow: initial; background: initial; }
  }
</style>
<body>
@php
  $earth = $earth ?? null;
  $details = ($details ?? collect())->values();
  // 將明細分成每頁 2 張（上下兩聯）
  $chunks = $details->chunk(2);
@endphp

<div id="printTableArea">
  @foreach ($chunks as $chunk)
    <div class="page">
      <div class="sheet">
        @foreach ($chunk as $idx => $d)
          <table class="block">
            <tr>
              <td class="no-border" style="font-size:20px;" width="45%">{{ $earth->company_name ?? '' }} 土單專用進場憑證</td>
              <td class="no-border title" width="30%">文件序號</td>
              <td class="no-border" width="25%">{{ $earth->batch_no ?? '' }}</td>
            </tr>
          </table>
          <table class="block">
            <tr>
              <td width="32%">工程流向管制編號：{{ $earth->flow_control_no ?? '' }}</td>
              <td width="18%">土質等級：{{ $earth->carry_soil_type ?? '' }}</td>
              <td width="20%">載運數量：{{ $earth->carry_qty ?? '' }}</td>
              <td width="30%">有效期間：{{ $earth->valid_date_from ?? '' }} ~ {{ $earth->valid_date_to ?? '' }}</td>
            </tr>
          </table>
          <table class="block">
            <tr>
              <td width="60%">工程名稱：{{ $earth->project_name ?? '' }}</td>
              <td width="40%">條碼：{{ $d->barcode }}</td>
            </tr>
          </table>
          <table class="block">
            <tr>
              <td width="70%" style="height:30mm; vertical-align:top;">
                注意事項：
                <div class="sub">
                  1. 本單核銷請攜回憑證及 QR 碼供稽核。
                  2. 本單僅限本工程使用，逾期無效。
                </div>
              </td>
              <td width="30%" class="text-center" style="vertical-align:middle;">
                {{-- 可置入 QR 圖（預留） --}}
                <div style="width:30mm;height:30mm;border:1px solid #000;margin:0 auto;display:flex;align-items:center;justify-content:center;">QR</div>
              </td>
            </tr>
          </table>
          @if ($idx === 0)
            <div style="height:6mm;"></div>
          @endif
        @endforeach
      </div>
    </div>
  @endforeach
</div>
<script>
  // 自動開印
  window.addEventListener('load', () => {
    setTimeout(() => window.print(), 300);
  });
</script>
</body>
</html>
