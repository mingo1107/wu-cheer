@extends('layout.base-print')

@section('style')
<style id="print-style">

body {
    margin: 0;
    padding: 0;
    background-color: #FAFAFA;
    font-size: 16pt;
    font-weight: normal !important;
    font-family: 'Courier New', 'Consolas', 'Lucida Console', monospace !important;
    /* -webkit-font-smoothing: none; */
}

table {
    font-family: 'Courier New', 'Consolas', 'Lucida Console', monospace !important;
    font-size: 16px;
    font-weight: normal !important;
    /* -webkit-font-smoothing: none; */
}

* {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
}

.page {
    padding: 0mm 1mm 0mm 1mm;
    margin: 0mm auto;
    border: 1px #D3D3D3 solid;
    border-radius: 4px;
    background: white;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    page-break-after: always;
}

.subpage {}

.table-txt {
    width: 200mm; /* A4最佳寬度，table-layout: fixed */
    table-layout: fixed;
    border-collapse: collapse;
    margin-bottom: 2px;
}
.table-txt th, .table-txt td {
    border: 1px solid #222;
    padding: 1px 1px;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.table-txt th {
    font-weight: bold;
}

table.table_4,
table.table_4 td {
    border-collapse: collapse;
    border: 1px solid #333;
}

table.table_4 td, 
table.table_4 th {
    border: 1px solid #000;
    text-align: left;
    padding: 0px;
}

.no-border {
  border: none !important;
}

.text-center {
    text-align: center !important;
}

.text-left {
    text-align: left !important;
}

.text-right {
    text-align: right !important;
}

.hr40 { border-bottom: 2px solid #000; }


@media print {
    @page {
        margin: 0mm;
        size: 215mm 145mm;
    }
    html,
    body {
        -webkit-print-color-adjust: exact;
        font-family: 'Courier New', monospace !important;
    }

    .page {
        margin: 0;
        border: initial;
        border-radius: initial;
        width: initial;
        min-height: initial;
        box-shadow: initial;
        background: initial;
        page-break-after: always;
    }

    .table-txt th, .table-txt td {
        font-size: 14px;
    }
}

.printDiv {
    /* font-size: 50px; */
    /* height: 145mm; */
    page-break-after: always;
}

</style>
@endsection

@section('content')

@php
    $dataInfo = $vueData['dataInfo'];
    $printMode = $vueData['printMode'];
    //$products = $vueData['recordList'][0] ?? [];
@endphp

<iframe id="printFrame" name="printFrame" style="display:none;"></iframe>
@if (request()->query('print') !== 'no')
<button type="button" onclick="printTableToIframe()">列印</button>
@endif

<div id="printTableArea">
    @foreach ($vueData['recordList'] as $products)
    <div class="page printDiv" style="padding: 5mm 3mm 0mm 2mm">
        <div class="subpage">
            <table class="table-txt" style="width:100%; margin-bottom:0;">
                <tr class="title-row">
                    <td style="border:none;font-size:22px;" width="35%">
                        @if (!empty($dataInfo['cmpShipLogo']))
                            <img src="{{ asset('storage/' . $dataInfo['cmpShipLogo']) }}" width="20">
                        @endif
                        {{ $dataInfo['cmpname'] }}
                    </td>
                    <td style="border:none;font-size:24px;" width="30%">
                        出貨單
                        <span style="float:center;font-size:20px;">{{ $dataInfo['taiwanDate'] }}</span>
                    </td>
                    <td style="border:none;font-size:14px;text-align:left;" width="35%">
                        訂貨專線:{{ $dataInfo['cmpOrder1'] }}&nbsp;{{ $dataInfo['cmpOrder2'] }}<br>
                        傳真號碼:{{ $dataInfo['cmpfax'] }}
                        <span style="float:right;">第{{ $loop->iteration }}頁/共{{ $loop->count }}頁</span>
                    </td>
                </tr>
            </table>  
            
            <table class="table-txt" style="width:100%; border-style: none; border:1px solid #000;">
                <thead>
                    <tr style="">
                        <td class="no-border" style="font-size: 18px !important; width:67mm;">公司:{{ $dataInfo['parnerName'] }}</td>
                        <td class="no-border" style="font-size: 18px !important; width:60mm;">客戶編號:
                            <span style="font-size: 16px !important;">{{ $dataInfo['panVIP'] }} {{ $dataInfo['panCode'] }}
                            </span>
                        </td>
                        <td class="no-border" style="font-size: 18px !important; width:53mm;">單據號碼:
                            <span style="font-size: 16px !important;">{{ $dataInfo['itsNumber'] }}</span>
                        </td>
                    </tr>
                </thead>
                <tr border="0" style="border-style: none; ">
                    <td class="no-border" style="font-size: 18px !important;">電話號碼:{{ $dataInfo['panPhone'] }}</td>
                    <td class="no-border" style="font-size: 18px !important;">行動電話:{{ $dataInfo['panMobile'] }} </td>
                    <td class="no-border" style="font-size: 18px !important;">發票號碼:{{ $dataInfo['itsSetInvoice'] }}</td>
                </tr>
                <tr border="0" style="border-style: none; ">
                    <td colspan="2" class="no-border" style="font-size: 18px !important;">送貨地址:{{ $dataInfo['panAddress'] }} </td>
                    <td class="no-border" style="font-size: 18px !important;">訂貨類別:{{ $dataInfo['itsTypeTxt'] }}</td>
                </tr>
            </table>

            <!-- 這段主商品明細table已最佳化A4欄寬 -->
            <table class="table-txt" style="width:100%; table-layout:fixed;">
                <thead>
                    <tr style="font-size: 22px !important">
                        <th class="text-center" style="width:8mm;">序</th>
                        <th class="text-center" style="width:18mm;">產品編號</th>
                        <th class="text-center" style="width:30mm;">品名規格</th>
                        <th class="text-center" style="width:13mm;">包裝</th>
                        <th class="text-center" style="width:10mm;">數量</th>
                        <th class="text-center" style="width:20mm;">出貨說明</th>
                        <th class="text-center" style="width:11mm;">單價</th>
                        <th class="text-center" style="width:10mm;">折數</th>
                        <th class="text-center" style="width:13mm;">金額</th>
                        <th class="text-center" style="width:20mm;">類別小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $pageIndex => $p)
                        @if(($p['pdtSaleName']??'') === '**以下空白**')
                            <tr style="height: 26px; font-size: 20px !important; border:none !important;">
                                <td colspan="10" style="text-align:left; padding-left: 10px; border-top:none !important; border-bottom:none !important; border-left:1px solid #000 !important; border-right:1px solid #000 !important;">**以下空白**</td>
                            </tr>
                        @elseif(empty($p['pdtSaleName']) && empty($p['itrQty']))
                            <tr style="height: 26px; font-size: 20px !important; border:none !important;">
                                <td colspan="10" style="text-align:left; padding-left: 10px; border-top:none !important; border-bottom:none !important; border-left:1px solid #000 !important; border-right:1px solid #000 !important;">&nbsp;&nbsp;</td>
                            </tr>
                        @else
                            <tr style="height: 26px; font-size: 20px !important">
                                <td class="text-center">{{ $p['rowIndex'] ?? '' }}</td>
                                <td>{{ $p['itrPdtid'] ?? '' }}</td>
                                <td>{{ $p['pdtSaleName'] ?? '' }}</td>
                                <td class="text-center">{{ $p['pdtUnit'] ?? '' }}</td>
                                <td class="text-right">{{ $p['itrQty'] ?? '' }}.</td>
                                <td class="text-left">{{ $p['itrNotes'] ?? '' }}</td>
                                <td class="text-right">
                                    @if($printMode != 1)
                                        {{ $p['itrPrice'] ?? '' }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($printMode == 2)
                                        {{ $p['itrDiscountText'] ?? '' }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($printMode == 0)    
                                        {{ $p['itrSum'] ?? '' }}
                                    @elseif($printMode == 2)   
                                        {{ $p['itrSumDiscount'] ?? '' }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($printMode != 1)   
                                    {{ $p['catalogSum'] ?? '' }}
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    <tr style="height: 30px;font-size: 20px !important">
                        <th colspan="7">
                            <span class="text-left">
                                @if (!empty($dataInfo['itsMemory']))
                                    重要資訊:{{ $dataInfo['itsMemory'] }}
                                @endif
                            </span>
                        </th>
                        <th colspan="1">
                            @if($loop->iteration == count($vueData['recordList']))
                                <div class="col-12 text-center">
                                    總計
                                </div>
                            @endif
                        </th>
                        <th colspan="2">
                            @if($loop->iteration == count($vueData['recordList']))
                                <div class="text-right" style="font-size:22px">
                                    @if($printMode == 0)
                                        {{ $dataInfo['itsTotal'] }}
                                    @elseif($printMode == 2)
                                        {{ $dataInfo['itsTotalWithDiscount'] }}
                                    @endif
                                </div>
                            @endif
                        </th>
                    </tr>
                </tbody>
            </table>

            <table class="table_4" style="width:100%;">
                <tbody>
                    <tr style="font-size: 18px !important">
                        <th width="200" style="text-align:left; font-size:14px">
                            (1){{ $dataInfo['catalogSum'][1] }}
                            (2){{ $dataInfo['catalogSum'][2] }}
                            (3){{ $dataInfo['catalogSum'][3] }}
                        </th>
                        <th width="50" class="text-center">{{ $vueData['pageQtyTotals'][$loop->index] ?? '' }}</th>
                        <th width="50" class="text-center">倉管</th>
                        <th width="80"></th>
                        <th width="50" class="text-center">經辦<br>業務</th>
                        <th width="200" style="font-size:14px; padding-left: 4px;">
                            {{ $dataInfo['pnlIDOld'] }} &emsp;{{ $dataInfo['pnlName'] }}
                            <br><span style="font-size:14px">{{ $dataInfo['printTime'] }}</span>
                        </th>
                        <th width="50" class="text-center">客戶<br>簽名</th>
                        <th width="150"></th>
                    </tr>
                </tbody>
            </table>
            {{-- @if($loop->iteration == count($vueData['recordList']) && !empty($dataInfo['panActivity']) )
            <div style="font-size:1.2rem;font-weight:bold">    
            營業時間：{{ $dataInfo['panActivity'] }}
            </div>
            @endif --}}
            <div style="font-size:1.0rem;font-weight:bold">
                @if($loop->iteration == count($vueData['recordList']) && !empty($dataInfo['congratulate']))
                    {{ $dataInfo['congratulate'] }}
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@section('js')
<script>
function printTableToIframe() {
    var content = document.getElementById('printTableArea').outerHTML;
    var css = document.getElementById('print-style').outerHTML;
    var frame = document.getElementById('printFrame');
    var idoc = frame.contentWindow.document;
    idoc.open();
    idoc.write('<html><head>' + css + '</head><body>' + content + '</body></html>');
    idoc.close();
    setTimeout(function() {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }, 300);
}
</script>

@endsection
