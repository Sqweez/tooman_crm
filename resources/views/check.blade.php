<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Чек</title>
</head>
<body>
<div class="check-wrapper" id="print" style="font-family: sans-serif; color: #000; width: 58mm; padding-right: 10mm">
    <div class="check-header"
         style="display: flex;

                flex-direction: column;">
        <p style="font-size: 8px; font-weight: lighter;">
            ==============================================================================================================</p>
        <p
            style="display:block;
                    text-align: center;
                    color: #000;
                    font-family: sans-serif;
                    text-transform: inherit;
                    font-size: 15px;
                    margin: 0;">
            {{ $report->store_meta['legal_name'] }}
        </p>
        <p STYLE="text-align: CENTER; font-size: 12px; margin: 0;">ДОБРО ПОЖАЛОВАТЬ!</p>
        <p STYLE="text-align: CENTER; font-size: 12px; margin: 0;">КАССОВЫЙ ЧЕК</p>
    </div>
    <p style="font-size: 12px;">
        ПРИХОД
    </p>
    <div style="display: flex; justify-content: space-between; align-items: center">
        <p style="margin: 0; font-size: 12px;">
            ЧЕК №{{ $report->id }}
        </p>
        <p style="margin: 0; font-size: 12px;">
            {{ $report->date }}
        </p>
    </div>
    <span style="white-space: nowrap; font-weight: lighter; font-size: 8px;">
        ------------------------------------------------------------------------------------------------------------------
    </span>
    <div class="check-body">
        <ul class="products-list" style="list-style: none; padding: 0;">
            @foreach ($report->products as $product)
                <li
                    style="color: #000;

                        font-size: 12px;
                        font-style: italic;
                        text-transform: uppercase;">
                    <span class="product-name">{{ $product['product_name']  }} | {{ $product['manufacturer']['manufacturer_name'] }} | {{ collect($product['attributes'])->join(' | ') }}</span>
                    <span class="product-footer"
                          style="display: flex;
                             justify-content: space-between;
                             margin-left: 20px;">
                                    <span class="product-count" style="
                                    white-space: nowrap;
                                    overflow: hidden;">
                                        {{ number_format($product['count'], 3) }} х  {{ number_format($product['product_price'], 2) }}..........................................................................................................................................................................................................
                                    </span>
                                     <span
                                         class="product-cost">{{ number_format($product['count'] * $product['product_price'], 2)  }}</span>
                                </span>
                </li>
            @endforeach
        </ul>
        @if ($report->discount != 0 || $report->client['id'] != -1)
            <div class="check-divider"
                 style="margin: 10px 0;
                    width: 58mm;
                    transform: skew(-15deg);
                    display: flex;
                    align-items: center;">
                <div class="divider-one"
                     style="flex: 1;
                        height: 2px;
                        background-color: #000;
                        margin-right: 7px;"></div>
                <div class="divider-two"
                     style="flex: 2;
                        height: 1px;
                        background-color: #000;"></div>
            </div>
            @if ($report->client['id'] != -1)
                <div
                    style="font-size: 12px;
                            text-transform: uppercase;
                            font-style: italic;"
                    class="client-info">
                    <div class="client-name"
                         style="display: flex;
                                justify-content: space-between;">
                        <span class="client-label"
                              style="white-space: nowrap;
                                     overflow: hidden;">
                            Ф.И.О......................................................................................................................
                        </span>
                        <span class="name"
                              style="white-space: normal;
                                     margin-left: 5px;">
                            {{ $report->client['client_name'] }}
                    </span>
                    </div>
                    @endif
                    @if($report->discount != 0)
                        <div class="client-discount"
                             style="display: flex;
                        justify-content: space-between;">
                <span class="client-label"
                      style="white-space: nowrap;
                             overflow: hidden;">
                    Скидка......................................................................................................................
                </span>
                            <span class="name"
                                  style="white-space: normal;
                             margin-left: 5px;">
                    {{ $report->discount }}%
                </span>
                        </div>
                    @endif
                </div>
            @endif
    </div>
    <span style="white-space: nowrap; font-weight: lighter; font-size: 8px;">
        ------------------------------------------------------------------------------------------------------------------
    </span>
    <div class="check-footer" style="width: 58mm;">
        @if($report->certificate)
            <div class="total"
                 style="display: flex;
                    justify-content: space-between;
                    text-transform: uppercase;">
            <span class="footer-label" style="font-size: 12px; white-space: nowrap; overflow: hidden;">
                <span class="label-red" style="font-size: 12px;">Сертификат</span>
                ..........................................................................................................................................................................................................................
            </span>
                <span class="span-total"
                      style="font-size: 12px; white-space: nowrap; margin-left: 5px;">{{ $report->certificate['amount'] }}</span>
            </div>
        @endif
        <div class="total"
             style="display: flex;
                    justify-content: space-between;
                    text-transform: uppercase;">
            <span class="footer-label" style="font-size: 12px; white-space: nowrap; overflow: hidden;">
                <span class="label-red" style="font-size: 12px;">ИТОГ</span>
                ..........................................................................................................................................................................................................................
            </span>
            <span class="span-total"
                  style="font-size: 12px; white-space: nowrap; margin-left: 5px;">{{ number_format($report->final_price - ($report->certificate['amount'] ?? 0), 2) }}</span>
        </div>
        <span style="white-space: nowrap; font-weight: lighter; font-size: 8px;">
            ------------------------------------------------------------------------------------------------------------------
        </span>
        <p style="font-size: 12px; margin: 0;">
            ОПЛАТА
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <p style="font-size: 12px; margin: 0; margin-left: 6px;">
                ЭЛЕКТРОННЫМИ
            </p>
            <p style="font-size: 12px; margin: 0;">
                ={{ number_format($report->final_price - ($report->certificate['amount'] ?? 0), 2) }}
            </p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <p style="font-size: 12px; margin: 0;">
                ПОЛУЧЕНО
            </p>
            <p style="font-size: 12px; margin: 0;">
                ={{ number_format($report->final_price - ($report->certificate['amount'] ?? 0), 2) }}
            </p>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <p style="font-size: 12px; margin: 0; margin-left: 6px;">
                СДАЧА
            </p>
            <p style="font-size: 12px; margin: 0;">
                =0.00
            </p>
        </div>
        <p style="font-size: 12px; margin: 0;">
            КАССИР: Администратор
        </p>
        <p style="font-size: 12px; margin: 0; margin-top: 7px;">
            ПОДПИСЬ:__________________________________________________
        </p>
        <h4 style="text-align: center; text-transform: uppercase; font-size: 15px; margin-top: 10px; margin-bottom: 40px; font-weight: bold;">
            Спасибо за покупку!</h4>
    </div>
    <p style="font-size: 8px; font-weight: lighter;">
        ==============================================================================================================</p>
    <p
    <div class="empty-space"></div>
</div>
<script>
    window.print();
    window.addEventListener('afterprint', (event) => {
        window.close();
    });
</script>
</body>
</html>
