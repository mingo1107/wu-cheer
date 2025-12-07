<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="utf-8" />
<title>竣工土方資源堆置場完成證明書</title>
<style>
  @page {
    size: A4;
    margin: 15mm;
  }

  body {
    margin: 0;
    padding: 20px;
    font-family: 'Microsoft JhengHei', 'Arial', sans-serif;
    background: #FFE4E1;
  }

  .certificate {
    width: 100%;
    max-width: 700px;
    margin: 0 auto;
    padding: 30px;
    border: 8px solid #333;
    border-image: repeating-linear-gradient(
      45deg,
      #333,
      #333 10px,
      transparent 10px,
      transparent 20px
    ) 8;
    position: relative;
    min-height: 900px;
    background: #FFE4E1;
  }

  .certificate-inner {
    border: 2px solid #666;
    padding: 25px;
    min-height: 850px;
    position: relative;
  }

  .title {
    text-align: center;
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
    letter-spacing: 8px;
  }

  .doc-number {
    text-align: right;
    font-size: 14px;
    margin-bottom: 30px;
    color: #666;
  }

  .content {
    font-size: 16px;
    line-height: 2.2;
    text-align: justify;
    margin-bottom: 40px;
    text-indent: 2em;
  }

  .content .company-name {
    text-decoration: underline;
    font-weight: bold;
  }

  .content .project-name {
    text-decoration: underline;
    font-weight: bold;
  }

  .content .numbers {
    text-decoration: underline;
    font-weight: bold;
  }

  .signature-section {
    margin-top: 60px;
    text-align: center;
  }

  .signature-line {
    margin-top: 20px;
    font-size: 16px;
  }

  .company-info {
    position: absolute;
    bottom: 30px;
    left: 40px;
    right: 40px;
    font-size: 14px;
    line-height: 1.8;
  }

  .company-info div {
    margin: 8px 0;
  }

  .date {
    text-align: center;
    font-size: 16px;
    margin-top: 40px;
    letter-spacing: 8px;
  }

  .stamp-area {
    position: absolute;
    right: 60px;
    bottom: 180px;
    width: 120px;
    height: 120px;
  }

  .photo-area {
    position: absolute;
    right: 40px;
    top: 350px;
    width: 200px;
    height: 150px;
    border: 2px solid #999;
    padding: 4px;
    background: white;
  }

  .photo-area img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .photo-caption {
    text-align: center;
    font-size: 11px;
    color: #666;
    margin-top: 4px;
  }

  @media print {
    body {
      background: white;
    }
    .certificate {
      background: #FFE4E1;
    }
  }
</style>
</head>
<body>
<div class="certificate">
  <div class="certificate-inner">
    <div class="title">竣工土方資源堆置場完成證明書</div>
    <div class="doc-number">NO.{{ str_pad($earth->id, 4, '0', STR_PAD_LEFT) }}</div>

    <div class="content">
      本公司同意　貴公司所承（起）運<span class="company-name">{{ $earth->customer_name ?? '___________' }}</span>

      事業部桃園縣濱新建工程（桃園市政府建造執照<span class="project-name">{{ $earth->flow_control_no ?? '(______)' }}</span>桃市都建執照

      字第會龜<span class="numbers">{{ str_pad($earth->id, 5, '0', STR_PAD_LEFT) }}</span>號）乙案，該工程開挖產生營建工程剩餘土石方量共

      <span class="numbers">{{ number_format($earth->carry_qty, 2) }}</span>立方公尺（土質 {{ is_array($earth->carry_soil_type) ? implode('、', $earth->carry_soil_type) : $earth->carry_soil_type }}）。上述營建工程剩餘土石方數量已全部載

      運完成無誤。（偽造必究，以下空白）
    </div>

    <div class="signature-section">
      <div class="signature-line">
        此致
      </div>
      <div class="signature-line">
        合億營造有限公司
      </div>
    </div>

    <div class="stamp-area">
      <!-- 印章區域 -->
    </div>

    @if(!empty($earth->closure_certificate_path))
    <div class="photo-area">
      <img src="{{ asset('storage/' . $earth->closure_certificate_path) }}" alt="結案照片" />
      <div class="photo-caption">結案現場照片</div>
    </div>
    @endif

    <div class="company-info">
      <div>立　書　人：富國工程股份有限公司－陳　俊　浩</div>
      <div>聯絡地址：新竹縣寶山鄉三峰村三峰路一段 269 號</div>
      <div>堆置場址：新竹縣寶山鄉三叉凸段 2554-0000 地號等 64 筆土地</div>
    </div>

    <div class="date">
      中　　華　　民　　國　　{{ $closedYear }}　年　　{{ $closedMonth }}　月　　{{ $closedDay }}　日
    </div>
  </div>
</div>
</body>
</html>
