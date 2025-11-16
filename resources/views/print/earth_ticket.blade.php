<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="utf-8" />
<title>土單列印</title>
<style id="print-style">
  body { margin:0; padding:0; background:#FAFAFA; font-family:'Courier New','Consolas','Lucida Console',monospace; }
  .page { padding: 8mm 5mm; page-break-after: always; }
  .sheet { width: 200mm; margin: 0 auto; }
  .ticket { width: 200mm; border:1px solid #000; padding: 3mm; }
  table { width: 100%; table-layout: fixed; border-collapse: collapse; font-size: 13px; }
  th,td { border:1px solid #000; padding:2px 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .no-border { border:none !important; }
  .title { font-size: 18px; font-weight: bold; text-align: center; }
  .sub { font-size: 12px; color:#444; }
  .block { margin-bottom: 4mm; }
  .row-gap { height: 5mm; }
  .dashed { border:1px dashed #000; }
  .sign-line { border-bottom:1px solid #000; height: 8mm; }

  /* 讓每頁剛好兩張：每張預留約 130mm 高度 + 間距 */
  .ticket-wrap { height: 134mm; display: flex; align-items: stretch; }

  @media print {
    @page { size: A4; margin: 8mm; }
    html,body { -webkit-print-color-adjust: exact; }
    .page { margin:0; border: initial; width: initial; min-height: initial; box-shadow: initial; background: initial; }
  }
</style>
</head>
<body>
@php
  $earth = $earth ?? null;
  $details = ($details ?? collect())->values();
  // 將明細分成每頁 2 張（上下兩聯）
  $chunks = $details->chunk(2);
@endphp

<div id="printTableArea">
  @if ($chunks->count() === 0)
    <div class="page">
      <div class="sheet" style="text-align:center;padding:20mm 0;">
        <div style="font-size:18px;">目前無未列印的憑證</div>
      </div>
    </div>
  @else
    @foreach ($chunks as $chunk)
      <div class="page">
        <div class="sheet">
          @foreach ($chunk as $idx => $d)
          <div class="ticket-wrap">
            <div class="ticket">
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
                  <td width="50%">承包廠商（清運業者）：{{ $earth->cleaner_name ?? '' }}</td>
                  <td width="50%">合法收容處理場所：{{ $earth->disposal_site_name ?? '' }}</td>
                </tr>
              </table>

              <table class="block">
                <tr>
                  <td width="50%">駕駛人與車牌號碼（簽名）：<div class="sign-line"></div></td>
                  <td width="50%">合法收容處理場所流向管制編號：{{ $earth->disposal_flow_control_no ?? '' }}</td>
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
                    <div id="qr-{{ $d->id }}" data-content="{{ $d->barcode }}" style="width:35mm;height:35mm;margin:0 auto;"></div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          @if ($idx === 0)
            <div class="row-gap"></div>
          @endif
          @endforeach
        </div>
      </div>
    @endforeach
  @endif
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  // 自動開印
//   window.addEventListener('load', () => {
//     setTimeout(() => window.print(), 300);
//   });
  // 產生 QRCode（內容預設使用每筆明細的 barcode）
  (function() {
    const nodes = document.querySelectorAll('[id^="qr-"]');
    nodes.forEach(function(n){
      const text = n.getAttribute('data-content') || '';
      try {
        new QRCode(n, { text, width: 140, height: 140 });
      } catch (e) {}
    });
  })();
</script>
</body>
</html>

