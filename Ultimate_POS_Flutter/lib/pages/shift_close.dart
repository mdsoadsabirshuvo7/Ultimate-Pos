import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../helpers/AppTheme.dart';
import '../helpers/otherHelpers.dart';
import '../models/paymentDatabase.dart';
import '../models/sellDatabase.dart';

class ShiftClose extends StatefulWidget {
  @override
  State<ShiftClose> createState() => _ShiftCloseState();
}

class _ShiftCloseState extends State<ShiftClose> {
  final TextEditingController _actualCashController = TextEditingController();

  DateTime _from = DateTime(DateTime.now().year, DateTime.now().month,
      DateTime.now().day, 0, 0, 0, 0, 0);
  DateTime _to = DateTime.now();

  bool _loading = true;
  int _saleCount = 0;
  double _grossSales = 0;
  double _totalPaid = 0;
  double _totalDue = 0;
  double _expectedCash = 0;
  double _actualCash = 0;
  double _cashDifference = 0;
  Map<String, double> _paymentTotals = {};

  @override
  void initState() {
    super.initState();
    _loadShiftReport();
  }

  @override
  void dispose() {
    _actualCashController.dispose();
    super.dispose();
  }

  Future<void> _loadShiftReport() async {
    setState(() {
      _loading = true;
    });

    final sells = await SellDatabase().getSells(all: true);

    int saleCount = 0;
    double grossSales = 0;
    double totalPaid = 0;
    double totalDue = 0;
    final paymentTotals = <String, double>{};

    for (final sell in sells) {
      final status = (sell['status'] ?? '').toString();
      final txDateRaw = sell['transaction_date']?.toString();
      final txDate = (txDateRaw != null && txDateRaw.isNotEmpty)
          ? DateTime.tryParse(txDateRaw)
          : null;

      if (status != 'final' || txDate == null) {
        continue;
      }
      if (txDate.isBefore(_from) || txDate.isAfter(_to)) {
        continue;
      }

      saleCount += 1;
      grossSales += _toDouble(sell['invoice_amount']);
      totalDue += _toDouble(sell['pending_amount']);

      final payments =
          await PaymentDatabase().get(sell['id'], allColumns: true);
      for (final payment in payments) {
        if ((payment['is_return'] ?? 0) == 1) {
          continue;
        }
        final method = (payment['method'] ?? 'other').toString();
        final amount = _toDouble(payment['amount']);
        totalPaid += amount;
        paymentTotals[method] = (paymentTotals[method] ?? 0) + amount;
      }
    }

    final expectedCash = paymentTotals['cash'] ?? 0;
    final double actualCash = _actualCashController.text.trim().isEmpty
      ? 0.0
      : _toDouble(_actualCashController.text);
    final cashDifference = actualCash - expectedCash;

    if (!mounted) return;
    setState(() {
      _saleCount = saleCount;
      _grossSales = grossSales;
      _totalPaid = totalPaid;
      _totalDue = totalDue;
      _paymentTotals = paymentTotals;
      _expectedCash = expectedCash;
      _actualCash = actualCash;
      _cashDifference = cashDifference;
      _loading = false;
    });
  }

  Future<void> _closeShift() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
        'last_shift_close_at', DateTime.now().toIso8601String());
    await prefs.setDouble('last_shift_expected_cash', _expectedCash);
    await prefs.setDouble('last_shift_actual_cash', _actualCash);
    await prefs.setDouble('last_shift_cash_difference', _cashDifference);

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Shift closed successfully')),
    );
  }

  void _showZReport() {
    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text('Z Report Summary'),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('Sales Count: $_saleCount'),
                Text('Gross Sales: ${Helper().formatCurrency(_grossSales)}'),
                Text('Total Paid: ${Helper().formatCurrency(_totalPaid)}'),
                Text('Total Due: ${Helper().formatCurrency(_totalDue)}'),
                const SizedBox(height: 8),
                const Text('Payment Breakdown:'),
                ..._paymentTotals.entries
                    .map((e) =>
                        Text('${e.key}: ${Helper().formatCurrency(e.value)}'))
                    .toList(),
                const SizedBox(height: 8),
                Text(
                    'Expected Cash: ${Helper().formatCurrency(_expectedCash)}'),
                Text('Actual Cash: ${Helper().formatCurrency(_actualCash)}'),
                Text('Difference: ${Helper().formatCurrency(_cashDifference)}'),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Close'),
            ),
          ],
        );
      },
    );
  }

  Widget _metricCard(String label, double value, ThemeData themeData) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: AppTheme.getTextStyle(themeData.textTheme.bodyMedium,
                  fontWeight: 600),
            ),
            const SizedBox(height: 4),
            Text(
              Helper().formatCurrency(value),
              style: AppTheme.getTextStyle(themeData.textTheme.titleLarge,
                  fontWeight: 700),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final themeData = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Shift Close',
          style: AppTheme.getTextStyle(themeData.textTheme.titleLarge,
              fontWeight: 600),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadShiftReport,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.schedule),
                      title: Text('Report Window'),
                      subtitle: Text(
                          '${_from.toString().split('.').first} -> ${_to.toString().split('.').first}'),
                      trailing: IconButton(
                        icon: const Icon(Icons.refresh),
                        onPressed: _loadShiftReport,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.receipt_long_outlined),
                      title: const Text('Finalized Sales Count'),
                      trailing: Text(
                        '$_saleCount',
                        style: AppTheme.getTextStyle(
                            themeData.textTheme.titleLarge,
                            fontWeight: 700),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  _metricCard('Gross Sales', _grossSales, themeData),
                  _metricCard('Total Paid', _totalPaid, themeData),
                  _metricCard('Total Due', _totalDue, themeData),
                  const SizedBox(height: 8),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Payment Breakdown',
                            style: AppTheme.getTextStyle(
                                themeData.textTheme.titleMedium,
                                fontWeight: 700),
                          ),
                          const SizedBox(height: 8),
                          if (_paymentTotals.isEmpty)
                            const Text('No payments found for this shift'),
                          ..._paymentTotals.entries.map((entry) {
                            return Padding(
                              padding: const EdgeInsets.symmetric(vertical: 4),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(entry.key),
                                  Text(Helper().formatCurrency(entry.value)),
                                ],
                              ),
                            );
                          }).toList(),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Cash Reconciliation',
                            style: AppTheme.getTextStyle(
                                themeData.textTheme.titleMedium,
                                fontWeight: 700),
                          ),
                          const SizedBox(height: 8),
                          Text(
                              'Expected Cash: ${Helper().formatCurrency(_expectedCash)}'),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _actualCashController,
                            keyboardType: TextInputType.number,
                            decoration: const InputDecoration(
                                labelText: 'Actual Cash Counted'),
                            onChanged: (value) {
                              setState(() {
                                _actualCash = _toDouble(value);
                                _cashDifference = _actualCash - _expectedCash;
                              });
                            },
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Difference: ${Helper().formatCurrency(_cashDifference)}',
                            style: AppTheme.getTextStyle(
                                themeData.textTheme.bodyLarge,
                                fontWeight: 700,
                                color: (_cashDifference < 0)
                                    ? Colors.red
                                    : Colors.green),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  FilledButton.icon(
                    onPressed: _showZReport,
                    icon: const Icon(Icons.summarize_outlined),
                    label: const Text('View Z Report'),
                  ),
                  const SizedBox(height: 8),
                  FilledButton.icon(
                    onPressed: _closeShift,
                    icon: const Icon(Icons.lock_clock_outlined),
                    label: const Text('Close Shift'),
                  ),
                ],
              ),
            ),
    );
  }

  double _toDouble(dynamic value) {
    if (value == null) return 0;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString()) ?? 0;
  }
}
