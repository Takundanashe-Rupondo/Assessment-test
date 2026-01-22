import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/models/user.dart';

void main() {
  group('User Model Tests', () {
    test('fromJson creates User with correct values', () {
      final json = {
        'id': 1,
        'name': 'John Doe',
        'email': 'john@example.com',
        'role': 'user',
      };

      final user = User.fromJson(json);

      expect(user.id, 1);
      expect(user.name, 'John Doe');
      expect(user.email, 'john@example.com');
      expect(user.role, 'user');
    });

    test('fromJson handles null role', () {
      final json = {
        'id': 2,
        'name': 'Jane Doe',
        'email': 'jane@example.com',
      };

      final user = User.fromJson(json);

      expect(user.id, 2);
      expect(user.name, 'Jane Doe');
      expect(user.email, 'jane@example.com');
      expect(user.role, null);
    });

    test('toJson returns correct map', () {
      final user = User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'admin',
      );

      final json = user.toJson();

      expect(json['id'], 1);
      expect(json['name'], 'John Doe');
      expect(json['email'], 'john@example.com');
      expect(json['role'], 'admin');
    });

    test('User equality works correctly', () {
      final user1 = User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'user',
      );

      final user2 = User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'user',
      );

      expect(user1, equals(user2));
    });

    test('User with different values is not equal', () {
      final user1 = User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'user',
      );

      final user2 = User(
        id: 2,
        name: 'Jane Doe',
        email: 'jane@example.com',
        role: 'admin',
      );

      expect(user1, isNot(equals(user2)));
    });
  });
}
