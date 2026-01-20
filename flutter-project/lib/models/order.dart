class Order {
  final int id;
  final int userId;
  final String status;
  final double totalAmount;
  final List<OrderItem>? items;

  Order({
    required this.id,
    required this.userId,
    required this.status,
    required this.totalAmount,
    this.items,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      userId: json['user_id'],
      status: json['status'],
      totalAmount: json['total_amount']?.toDouble() ?? 0.0,
      items: json['items'] != null
          ? (json['items'] as List)
              .map((item) => OrderItem.fromJson(item))
              .toList()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'status': status,
      'total_amount': totalAmount,
      'items': items?.map((item) => item.toJson()).toList(),
    };
  }
}

class OrderItem {
  final int id;
  final int productId;
  final int quantity;
  final double price;

  OrderItem({
    required this.id,
    required this.productId,
    required this.quantity,
    required this.price,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      productId: json['product_id'],
      quantity: json['quantity'],
      price: json['price']?.toDouble() ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'quantity': quantity,
      'price': price,
    };
  }
}

