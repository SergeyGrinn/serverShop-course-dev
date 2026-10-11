<?php
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$invoiceTotal = round((float) $order['total_price'], 2);
$vatAmount = round($invoiceTotal * 21 / 121, 2);
$invoiceSubtotal = round($invoiceTotal - $vatAmount, 2);
$chunkToWords = static function (int $number): string {
    $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    $words = [];

    if ($number >= 100) {
        $words[] = $ones[intdiv($number, 100)] . ' hundred';
        $number %= 100;
    }
    if ($number >= 20) {
        $word = $tens[intdiv($number, 10)];
        if ($number % 10 > 0) {
            $word .= ' ' . $ones[$number % 10];
        }
        $words[] = $word;
    } elseif ($number > 0) {
        $words[] = $ones[$number];
    }

    return implode(' ', $words);
};
$numberToWords = static function (int $number) use ($chunkToWords): string {
    if ($number === 0) {
        return 'zero';
    }

    $scales = ['', 'thousand', 'million', 'billion'];
    $groups = [];
    $scaleIndex = 0;
    while ($number > 0) {
        $chunk = $number % 1000;
        if ($chunk > 0) {
            $group = $chunkToWords($chunk);
            if ($scales[$scaleIndex] !== '') {
                $group .= ' ' . $scales[$scaleIndex];
            }
            array_unshift($groups, $group);
        }
        $number = intdiv($number, 1000);
        $scaleIndex++;
    }

    return implode(' ', $groups);
};
$minorUnits = (int) round($invoiceTotal * 100);
$wholeEuros = intdiv($minorUnits, 100);
$cents = $minorUnits % 100;
$invoiceTotalInWords = ucfirst($numberToWords($wholeEuros))
    . ($wholeEuros === 1 ? ' euro' : ' euros')
    . ' and ' . $numberToWords($cents)
    . ($cents === 1 ? ' cent' : ' cents');
$sellerName = trim((string) ($invoiceSeller['invoice_company_name'] ?? '')) ?: 'Server Catalog';
$sellerAddress = implode(', ', array_filter([
    $invoiceSeller['invoice_address'] ?? '',
    $invoiceSeller['invoice_postal_code'] ?? '',
    $invoiceSeller['invoice_city'] ?? '',
    $invoiceSeller['invoice_country'] ?? '',
], static fn($value) => trim((string) $value) !== ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 38px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        h1 { margin: 0; color: #173b5d; font-size: 26px; }
        h2 { margin: 0 0 10px; color: #173b5d; font-size: 15px; }
        .top { width: 100%; margin-bottom: 28px; }
        .top td { vertical-align: top; }
        .muted { color: #64748b; }
        .status { display: inline-block; padding: 6px 10px; border-radius: 4px; background: #e8f0f8; color: #173b5d; font-weight: bold; }
        .section { margin: 0 0 22px; }
        .box { padding: 14px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .customer { width: 100%; }
        .customer td { width: 50%; vertical-align: top; }
        .seller-details { margin: 0 0 4px; }
        .label { margin-bottom: 4px; color: #64748b; font-size: 10px; text-transform: uppercase; }
        .value { margin-bottom: 12px; font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; }
        .items th { padding: 9px 7px; border-bottom: 1px solid #94a3b8; background: #e8f0f8; color: #173b5d; text-align: left; }
        .items td { padding: 9px 7px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .items .component-row td { padding-top: 3px; padding-bottom: 3px; color: #475569; font-size: 10px; }
        .right { text-align: right; }
        .components { margin-top: 5px; color: #475569; font-size: 10px; }
        .total { width: 250px; margin: 16px 0 0 auto; border-collapse: collapse; }
        .total td { padding: 6px; }
        .grand-total { border-top: 1px solid #64748b; color: #173b5d; font-size: 15px; font-weight: bold; }
        .footer { margin-top: 36px; padding-top: 10px; border-top: 1px solid #cbd5e1; color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <table class="top">
        <tr>
            <td>
                <h1>Invoice</h1>
                <p class="muted">Server Catalog</p>
            </td>
            <td class="right">
                <strong>Invoice #<?= (int) $order['id'] ?></strong><br>
                <span class="muted">Issued <?= $escape(date('M j, Y H:i', strtotime($order['created_at']))) ?></span><br><br>
                <span class="status"><?= $escape($orderStatus) ?></span>
            </td>
        </tr>
    </table>

    <section class="section box">
        <h2>Seller</h2>
        <table class="customer">
            <tr>
                <td>
                    <strong><?= $escape($sellerName) ?></strong>
                    <?php if ($sellerAddress !== ''): ?><div class="seller-details"><?= $escape($sellerAddress) ?></div><?php endif; ?>
                    <?php if (!empty($invoiceSeller['invoice_registration_number'])): ?><div class="seller-details">Registration No.: <?= $escape($invoiceSeller['invoice_registration_number']) ?></div><?php endif; ?>
                    <?php if (!empty($invoiceSeller['invoice_vat_number'])): ?><div class="seller-details">VAT No.: <?= $escape($invoiceSeller['invoice_vat_number']) ?></div><?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($invoiceSeller['invoice_email'])): ?><div class="seller-details">Email: <?= $escape($invoiceSeller['invoice_email']) ?></div><?php endif; ?>
                    <?php if (!empty($invoiceSeller['invoice_phone'])): ?><div class="seller-details">Phone: <?= $escape($invoiceSeller['invoice_phone']) ?></div><?php endif; ?>
                    <?php if (!empty($invoiceSeller['invoice_bank_account'])): ?><div class="seller-details">Bank account: <?= $escape($invoiceSeller['invoice_bank_account']) ?></div><?php endif; ?>
                </td>
            </tr>
        </table>
    </section>

    <section class="section box">
        <h2>Customer</h2>
        <table class="customer">
            <tr>
                <td>
                    <div class="label">Name</div>
                    <div class="value"><?= $escape($order['name']) ?></div>
                    <div class="label">Email</div>
                    <div class="value"><?= $escape($order['email']) ?></div>
                </td>
                <td>
                    <?php if (!empty($order['phone'])): ?>
                        <div class="label">Phone</div>
                        <div class="value"><?= $escape($order['phone']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($order['comment'])): ?>
                        <div class="label">Order comments</div>
                        <div class="value"><?= nl2br($escape($order['comment'])) ?></div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </section>

    <section class="section">
        <h2>Order items</h2>
        <table class="items">
            <thead>
                <tr>
                    <th>Server and components</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td>
                            <strong><?= $escape($item['server_name']) ?></strong>
                            <?php if (!empty($item['server_description'])): ?>
                                <div class="components"><?= nl2br($escape($item['server_description'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="right">€<?= number_format((float) $item['server_base_price'], 2) ?></td>
                    </tr>
                    <?php foreach ($item['components'] as $component): ?>
                        <tr class="component-row">
                            <td>+ <?= $escape($component['name']) ?>: <?= $escape($component['value']) ?></td>
                            <td class="right">€<?= number_format((float) $component['price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="total">
            <tr><td>Subtotal (excl. VAT)</td><td class="right">€<?= number_format($invoiceSubtotal, 2) ?></td></tr>
            <tr><td>VAT (21%, included)</td><td class="right">€<?= number_format($vatAmount, 2) ?></td></tr>
            <tr><td>Shipping</td><td class="right">€0.00</td></tr>
            <tr class="grand-total"><td>Total incl. VAT</td><td class="right">€<?= number_format($invoiceTotal, 2) ?></td></tr>
        </table>
        <p class="components"><strong>Amount in words:</strong> <?= $escape($invoiceTotalInWords) ?></p>
    </section>

    <div class="footer">Invoice for order #<?= (int) $order['id'] ?> · Server Catalog</div>
</body>
</html>
