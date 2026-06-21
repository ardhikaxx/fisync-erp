<?php
$models = [
    'Accounting' => ['Transaction', 'JournalEntry', 'ChartOfAccount', 'FiscalPeriod', 'FiscalYear', 'Currency', 'ExchangeRate', 'Branch', 'Department'],
    'AR' => ['Invoice', 'InvoiceItem', 'Receipt', 'Customer'],
    'AP' => ['PurchaseOrder', 'PurchaseOrderItem', 'GoodsReceiptNote', 'GoodsReceiptItem', 'SupplierInvoice', 'ApPayment', 'Supplier'],
    'Asset' => ['FixedAsset', 'AssetDepreciationSchedule'],
    'Budget' => ['CostCenter', 'Budget'],
    'Tax' => ['TaxSetting', 'WithholdingTaxTransaction', 'TaxInvoiceNumber'],
    'Approval' => ['ApprovalRule', 'ApprovalRuleLevel', 'Approval', 'ApprovalLog'],
    'Audit' => ['AuditTrail'],
    'CashBank' => ['Bank', 'BankAccount', 'CashBankTransaction', 'TransactionCategory'],
    'Master' => ['Product']
];

foreach ($models as $folder => $classes) {
    $dir = __DIR__ . '/app/Models/' . $folder;
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    foreach ($classes as $class) {
        $file = $dir . '/' . $class . '.php';
        if (!file_exists($file)) {
            file_put_contents($file, "<?php\n\nnamespace App\\Models\\$folder;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass $class extends Model\n{\n    protected \$guarded = [];\n}\n");
        }
    }
}
echo "Models generated.\n";
