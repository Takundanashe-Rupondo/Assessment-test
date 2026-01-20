import 'package:flutter/material.dart';
import '../models/product.dart';

class ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onTap;

  const ProductCard({
    super.key,
    required this.product,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.all(8.0),
      child: ListTile(
        title: Text(product.name),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('\$${product.price.toStringAsFixed(2)}'),
          ],
        ),
        trailing: product.isInStock
            ? const Icon(Icons.shopping_cart)
            : const Text(
                'Out of Stock',
                style: TextStyle(color: Colors.red),
              ),
        onTap: onTap,
      ),
    );
  }
}

