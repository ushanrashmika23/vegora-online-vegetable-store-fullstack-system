<?php

class InvoicePdfService {
    // Generates a simple one-page PDF invoice and saves it to target path.
    public function generateInvoice($targetPath, array $invoiceData) {
        $lines = $this->buildLines($invoiceData);
        $content = $this->buildPdfTextContent($lines);
        $pdf = $this->buildPdfDocument($content);

        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($targetPath, $pdf) !== false;
    }

    private function buildLines(array $data) {
        $orderId = (int)($data['order_id'] ?? 0);
        $createdAt = (string)($data['created_at'] ?? date('Y-m-d H:i:s'));
        $customerName = (string)($data['customer_name'] ?? 'Customer');
        $customerEmail = (string)($data['customer_email'] ?? '');
        $shippingAddress = (string)($data['shipping_address'] ?? '');
        $city = (string)($data['shipping_city'] ?? '');
        $zip = (string)($data['shipping_zip'] ?? '');
        $phone = (string)($data['shipping_phone'] ?? '');
        $notes = (string)($data['shipping_notes'] ?? '');
        $items = $data['items'] ?? [];

        $subtotal = (float)($data['subtotal'] ?? 0);
        $discount = (float)($data['discount'] ?? 0);
        $shipping = (float)($data['shipping'] ?? 0);
        $tax = (float)($data['tax'] ?? 0);
        $total = (float)($data['total'] ?? 0);

        $lines = [];
        $lines[] = 'Vegora - INVOICE';
        $lines[] = 'Invoice #: INV-' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT);
        $lines[] = 'Order #: ' . $orderId;
        $lines[] = 'Date: ' . date('M d, Y h:i A', strtotime($createdAt));
        $lines[] = '';
        $lines[] = 'Bill To:';
        $lines[] = $customerName;
        if ($customerEmail !== '') {
            $lines[] = $customerEmail;
        }
        if ($shippingAddress !== '') {
            $lines[] = $shippingAddress;
        }
        $cityLine = trim($city . ' ' . $zip);
        if ($cityLine !== '') {
            $lines[] = $cityLine;
        }
        if ($phone !== '') {
            $lines[] = 'Phone: ' . $phone;
        }
        if ($notes !== '') {
            $lines[] = 'Note: ' . $notes;
        }

        $lines[] = '';
        $lines[] = 'Items:';

        foreach ($items as $item) {
            $name = (string)($item['name'] ?? 'Item');
            $qty = (int)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $lineTotal = $qty * $price;
            $lines[] = sprintf('- %s x%d @ $%.2f = $%.2f', $name, $qty, $price, $lineTotal);
        }

        $lines[] = '';
        $lines[] = sprintf('Subtotal: $%.2f', $subtotal);
        $lines[] = sprintf('Discount: -$%.2f', $discount);
        $lines[] = sprintf('Shipping: $%.2f', $shipping);
        $lines[] = sprintf('Tax: $%.2f', $tax);
        $lines[] = sprintf('TOTAL: $%.2f', $total);
        $lines[] = '';
        $lines[] = 'Thank you for shopping with Vegora.';

        return $lines;
    }

    private function buildPdfTextContent(array $lines) {
        $commands = [
            'BT',
            '/F1 11 Tf',
            '50 790 Td'
        ];

        $lineHeight = 15;
        $firstLine = true;
        foreach ($lines as $line) {
            $safe = $this->escapePdfString($line);
            if ($firstLine) {
                $commands[] = '(' . $safe . ') Tj';
                $firstLine = false;
            } else {
                $commands[] = '0 -' . $lineHeight . ' Td';
                $commands[] = '(' . $safe . ') Tj';
            }
        }

        $commands[] = 'ET';

        return implode("\n", $commands);
    }

    private function buildPdfDocument($streamContent) {
        $objects = [];

        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj';
        $objects[] = '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        $streamLength = strlen($streamContent);
        $objects[] = '5 0 obj << /Length ' . $streamLength . ' >> stream' . "\n" . $streamContent . "\n" . 'endstream endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n" . $xrefPosition . "\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    private function escapePdfString($str) {
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('(', '\\(', $str);
        $str = str_replace(')', '\\)', $str);
        $str = preg_replace('/[^\x20-\x7E]/', '?', $str);
        return $str;
    }
}
