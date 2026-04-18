import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../helpers/AppTheme.dart';
import '../helpers/SizeConfig.dart';
import '../helpers/otherHelpers.dart';
import '../models/database.dart';

class DailyDashboard extends StatefulWidget {
  @override
  _DailyDashboardState createState() => _DailyDashboardState();
}

class _DailyDashboardState extends State<DailyDashboard> {
  late ThemeData themeData;
  late CustomAppTheme customAppTheme;
  
  bool _isLoading = true;
  double _totalSalesAmount = 0.0;
  int _totalSalesCount = 0;
  double _totalDiscount = 0.0;
  Map<String, double> _paymentSplits = {};
  
  String businessSymbol = '';

  @override
  void initState() {
    super.initState();
    _fetchDailyStats();
  }

  Future<void> _fetchDailyStats() async {
    setState(() {
      _isLoading = true;
    });

    final details = await Helper().getFormattedBusinessDetails();
    businessSymbol = details['symbol'];

    final db = await DbProvider().database;
    final today = DateFormat('yyyy-MM-dd').format(DateTime.now());

    // Only get completed sales that are not quotations for today
    final sales = await db.query(
      'sell',
      where: 'is_quotation = 0 AND status = ? AND date(transaction_date) = ?',
      whereArgs: ['final', today],
    );

    double salesAmount = 0.0;
    double discountTotal = 0.0;
    int salesCount = sales.length;

    for (var sale in sales) {
      salesAmount += double.tryParse(sale['invoice_amount']?.toString() ?? '0') ?? 0.0;
      
      // Calculate discount amount
      double discount = double.tryParse(sale['discount_amount']?.toString() ?? '0') ?? 0.0;
      if (sale['discount_type'] == 'percentage') {
        // Approximation if subtotal isn't readily available, or we might need sell_lines.
        // Assuming discount_amount is the calculated value in DB if possible, or we calculate based on invoice_amount + discount.
        // Note: Sometimes discount_amount in DB is the absolute value even if type is percentage.
      }
      discountTotal += discount;
    }

    // Now fetch payments for these sales
    final Map<String, double> splits = {};
    if (sales.isNotEmpty) {
      final sellIds = sales.map((s) => s['id']).toList();
      final placeholders = List.filled(sellIds.length, '?').join(',');
      final payments = await db.query(
        'sell_payments',
        where: 'sell_id IN ($placeholders) AND is_return = 0',
        whereArgs: sellIds,
      );

      for (var payment in payments) {
        final method = payment['method']?.toString() ?? 'unknown';
        final amount = double.tryParse(payment['amount']?.toString() ?? '0') ?? 0.0;
        
        splits[method] = (splits[method] ?? 0.0) + amount;
      }
    }

    setState(() {
      _totalSalesAmount = salesAmount;
      _totalSalesCount = salesCount;
      _totalDiscount = discountTotal;
      _paymentSplits = splits;
      _isLoading = false;
    });
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Card(
      elevation: 2,
      margin: EdgeInsets.symmetric(vertical: MySize.size8!),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(MySize.size8!)),
      child: Padding(
        padding: EdgeInsets.all(MySize.size16!),
        child: Row(
          children: [
            Container(
              padding: EdgeInsets.all(MySize.size12!),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: MySize.size24),
            ),
            SizedBox(width: MySize.size16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: AppTheme.getTextStyle(
                      themeData.textTheme.bodyMedium,
                      color: themeData.colorScheme.onBackground.withOpacity(0.7),
                    ),
                  ),
                  SizedBox(height: MySize.size4),
                  Text(
                    value,
                    style: AppTheme.getTextStyle(
                      themeData.textTheme.titleMedium,
                      color: themeData.colorScheme.onBackground,
                      fontWeight: 700,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    themeData = Theme.of(context);
    customAppTheme = AppTheme.getCustomAppTheme(1);

    return Scaffold(
      backgroundColor: themeData.scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(
          "Daily Shift Analytics",
          style: AppTheme.getTextStyle(themeData.textTheme.titleLarge, fontWeight: 600),
        ),
        backgroundColor: themeData.appBarTheme.backgroundColor,
        elevation: 0,
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchDailyStats,
              child: SingleChildScrollView(
                physics: AlwaysScrollableScrollPhysics(),
                padding: EdgeInsets.all(MySize.size16!),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Today's Overview",
                      style: AppTheme.getTextStyle(
                        themeData.textTheme.titleMedium,
                        fontWeight: 700,
                      ),
                    ),
                    SizedBox(height: MySize.size16),
                    
                    _buildStatCard(
                      "Total Sales",
                      "$businessSymbol ${Helper().formatCurrency(_totalSalesAmount)}",
                      Icons.point_of_sale,
                      Colors.blue,
                    ),
                    _buildStatCard(
                      "Transactions Count",
                      "$_totalSalesCount",
                      Icons.receipt_long,
                      Colors.indigo,
                    ),
                    _buildStatCard(
                      "Total Discount",
                      "$businessSymbol ${Helper().formatCurrency(_totalDiscount)}",
                      Icons.discount,
                      Colors.orange,
                    ),
                    
                    SizedBox(height: MySize.size24),
                    Text(
                      "Payment Methods Split",
                      style: AppTheme.getTextStyle(
                        themeData.textTheme.titleMedium,
                        fontWeight: 700,
                      ),
                    ),
                    SizedBox(height: MySize.size16),
                    
                    if (_paymentSplits.isEmpty)
                      Center(
                        child: Padding(
                          padding: EdgeInsets.all(MySize.size24!),
                          child: Text("No payments recorded today.", style: AppTheme.getTextStyle(themeData.textTheme.bodyMedium)),
                        ),
                      ),
                      
                    ..._paymentSplits.entries.map((entry) {
                      String method = entry.key.toUpperCase();
                      double amount = entry.value;
                      return Card(
                        elevation: 1,
                        margin: EdgeInsets.only(bottom: MySize.size8!),
                        child: ListTile(
                          leading: Icon(
                            method == 'CASH' ? Icons.money : Icons.credit_card,
                            color: Colors.green,
                          ),
                          title: Text(
                            method,
                            style: AppTheme.getTextStyle(themeData.textTheme.bodyLarge, fontWeight: 600),
                          ),
                          trailing: Text(
                            "$businessSymbol ${Helper().formatCurrency(amount)}",
                            style: AppTheme.getTextStyle(themeData.textTheme.titleMedium, fontWeight: 700),
                          ),
                        ),
                      );
                    }).toList(),
                  ],
                ),
              ),
            ),
    );
  }
}
