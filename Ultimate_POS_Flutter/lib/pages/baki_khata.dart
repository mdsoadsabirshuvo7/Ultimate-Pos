import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:material_design_icons_flutter/material_design_icons_flutter.dart';
import 'package:intl/intl.dart';

import '../apis/api.dart';
import '../apis/contact.dart';
import '../apis/sell.dart';
import '../apis/contact_payment.dart';
import '../helpers/AppTheme.dart';
import '../helpers/SizeConfig.dart';
import '../helpers/otherHelpers.dart';
import '../locale/MyLocalizations.dart';
import '../models/system.dart';
import 'forms.dart';

class BakiKhata extends StatefulWidget {
  @override
  _BakiKhataState createState() => _BakiKhataState();
}

class _BakiKhataState extends State<BakiKhata> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  
  bool isLoading = true;
  List<Map> customerList = [];
  ScrollController customerListController = ScrollController();
  var searchController = TextEditingController();
  
  String? fetchCustomers = Api().apiUrl + "contactapi?type=customer&per_page=15";
  
  static int themeType = 1;
  ThemeData themeData = AppTheme.getThemeFromThemeMode(themeType);
  CustomAppTheme customAppTheme = AppTheme.getCustomAppTheme(themeType);
  String businessSymbol = '';

  @override
  void initState() {
    super.initState();
    _loadBusinessDetails();
    setCustomersList();
    customerListController.addListener(() {
      if (customerListController.position.pixels == customerListController.position.maxScrollExtent) {
        if (fetchCustomers != null && !isLoading) {
          setCustomersList();
        }
      }
    });
  }

  _loadBusinessDetails() async {
    await Helper().getFormattedBusinessDetails().then((value) {
      if (mounted) {
        setState(() {
          businessSymbol = value['symbol'] ?? '';
        });
      }
    });
  }

  setCustomersList() async {
    setState(() {
      isLoading = true;
    });
    final dio = Dio();
    var token = await System().getToken();
    dio.options.headers['content-Type'] = 'application/json';
    dio.options.headers["Authorization"] = "Bearer $token";
    try {
      final response = await dio.get(fetchCustomers!);
      List customers = response.data['data'];
      Map links = response.data['links'];
      if (mounted) {
        setState(() {
          customers.forEach((element) {
            customerList.add(element);
          });
          isLoading = (links['next'] != null) ? true : false;
          fetchCustomers = links['next'];
        });
      }
    } catch (e) {
      if(mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  void _searchCustomer(String text) {
    setState(() {
      customerList = [];
      fetchCustomers = Api().apiUrl + "contactapi?type=customer&per_page=15&name=" + text;
      setCustomersList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: Text('Baki Khata',
            style: AppTheme.getTextStyle(themeData.textTheme.titleLarge, fontWeight: 600)),
        elevation: 0,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          // Navigating to contacts directory to add customer. In future, implement instant inline add.
          Navigator.pushNamed(context, '/contacts');
        },
        icon: Icon(Icons.person_add),
        label: Text("New Customer"),
        backgroundColor: themeData.colorScheme.primary,
      ),
      body: Column(
        children: [
          Container(
            padding: EdgeInsets.all(MySize.size16!),
            color: themeData.scaffoldBackgroundColor,
            child: TextField(
              controller: searchController,
              decoration: InputDecoration(
                hintText: "Search customer...",
                prefixIcon: Icon(Icons.search),
                border: OutlineBinding(),
                filled: true,
                fillColor: customAppTheme.bgLayer1,
                contentPadding: EdgeInsets.all(MySize.size12!),
              ),
              onSubmitted: (value) => _searchCustomer(value),
              onChanged: (value) {
                // debounce Optional
              },
            ),
          ),
          Expanded(
            child: customerList.isEmpty && !isLoading
                ? Center(child: Text("No customers found"))
                : ListView.builder(
                    controller: customerListController,
                    padding: EdgeInsets.all(MySize.size12!),
                    itemCount: customerList.length + 1,
                    itemBuilder: (context, index) {
                      if (index == customerList.length) {
                        return isLoading ? Center(child: CircularProgressIndicator()) : SizedBox();
                      }
                      var customer = customerList[index];
                      double due = 0.0;
                      if(customer['balance'] != null) {
                         due = double.tryParse(customer['balance'].toString()) ?? 0.0;
                      } else if(customer['total_due'] != null) {
                         due = double.tryParse(customer['total_due'].toString()) ?? 0.0;
                      }
                      
                      return Card(
                        margin: EdgeInsets.only(bottom: MySize.size8!),
                        elevation: 2,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        child: Padding(
                          padding: EdgeInsets.all(MySize.size12!),
                          child: Row(
                            children: [
                              CircleAvatar(
                                backgroundColor: themeData.colorScheme.primary.withAlpha(40),
                                child: Text(customer['name'][0].toUpperCase(), style: TextStyle(color: themeData.colorScheme.primary)),
                              ),
                              SizedBox(width: MySize.size12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(customer['name'], style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                    SizedBox(height: 4),
                                    Text(customer['mobile'] ?? '-', style: TextStyle(color: Colors.grey[600], fontSize: 12)),
                                    SizedBox(height: 4),
                                    Text("Due:  ",
                                      style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15,
                                          color: due > 0 ? Colors.red : Colors.green)),
                                  ],
                                ),
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Row(
                                    children: [
                                      OutlinedButton(
                                        onPressed: () => _showAddBakiDialog(customer),
                                        child: Text("Add Baki", style: TextStyle(color: Colors.red)),
                                        style: OutlinedButton.styleFrom(
                                          side: BorderSide(color: Colors.red),
                                          padding: EdgeInsets.symmetric(horizontal: 8),
                                          minimumSize: Size(0, 32),
                                        ),
                                      ),
                                      SizedBox(width: 8),
                                      ElevatedButton(
                                        onPressed: () => _showSettleBakiDialog(customer),
                                        child: Text("Settle"),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.green,
                                          padding: EdgeInsets.symmetric(horizontal: 8),
                                          minimumSize: Size(0, 32),
                                        ),
                                      )
                                    ],
                                  )
                                ],
                              )
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }

  OutlineInputBorder OutlineBinding() {
    return OutlineInputBorder(
      borderRadius: BorderRadius.all(Radius.circular(MySize.size8!)),
      borderSide: BorderSide.none,
    );
  }

  void _showAddBakiDialog(Map customer) {
    TextEditingController amountController = TextEditingController();
    TextEditingController noteController = TextEditingController();
    
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text("Add Baki for "),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: amountController,
                keyboardType: TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(labelText: "Amount ()", border: OutlineInputBorder()),
              ),
              SizedBox(height: 10),
              TextField(
                controller: noteController,
                decoration: InputDecoration(labelText: "Note (Optional)", border: OutlineInputBorder()),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: Text("Cancel")),
            ElevatedButton(
              onPressed: () {
                if (amountController.text.isEmpty) return;
                Navigator.pop(context);
                _processAddBaki(customer, double.parse(amountController.text), noteController.text);
              },
              child: Text("Add", style: TextStyle(color: Colors.white)),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            ),
          ],
        );
      }
    );
  }

  void _showSettleBakiDialog(Map customer) {
    TextEditingController amountController = TextEditingController();
    TextEditingController noteController = TextEditingController();
    
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text("Settle Baki for "),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
               TextField(
                controller: amountController,
                keyboardType: TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(labelText: "Amount ()", border: OutlineInputBorder()),
              ),
              SizedBox(height: 10),
              TextField(
                controller: noteController,
                decoration: InputDecoration(labelText: "Note (Optional)", border: OutlineInputBorder()),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: Text("Cancel")),
            ElevatedButton(
              onPressed: () {
                if (amountController.text.isEmpty) return;
                Navigator.pop(context);
                _processSettleBaki(customer, double.parse(amountController.text), noteController.text);
              },
              child: Text("Settle", style: TextStyle(color: Colors.white)),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
            ),
          ],
        );
      }
    );
  }

  void _processAddBaki(Map customer, double amount, String note) async {
    // Add baki placeholder
    Fluttertoast.showToast(msg: "Baki logic wiring required in backend");
  }

  void _processSettleBaki(Map customer, double amount, String note) async {
    // Settle baki
    setState(() => isLoading = true);
    var paymentData = {
      'contact_id': customer['id'].toString(),
      'amount': amount.toString(),
      'method': 'cash',
      'paid_on': DateFormat('yyyy-MM-dd HH:mm:ss').format(DateTime.now()),
      'note': note
    };
    
    var result = await ContactPaymentApi().postContactPayment(paymentData);
    if (result != null && result == 200 || result == 201) {
      Fluttertoast.showToast(msg: "Payment Received!");
      _searchCustomer("");
    } else {
      Fluttertoast.showToast(msg: "Payment Failed", backgroundColor: Colors.red);
      setState(() => isLoading = false);
    }
  }
}
