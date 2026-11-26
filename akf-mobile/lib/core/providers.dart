import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'api/api_client.dart';
import 'api/ticket_repository.dart';
import 'auth/auth_repository.dart';
import 'storage/secure_storage.dart';

final apiProvider = Provider<ApiClient>((ref) => ApiClient());
final storageProvider = Provider<SecureStorage>((ref) => SecureStorage());
final authProvider = Provider<AuthRepository>(
  (ref) => AuthRepository(ref.watch(apiProvider), ref.watch(storageProvider)),
);
final ticketsProvider = Provider<TicketRepository>(
  (ref) => TicketRepository(ref.watch(apiProvider)),
);
