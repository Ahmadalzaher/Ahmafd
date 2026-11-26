import 'package:uuid/uuid.dart';

class BarcodeParser {
  static String normalize(String raw) {
    var value = raw.trim();
    if (value.isEmpty) return '';
    final uri = Uri.tryParse(value);
    if (uri != null && uri.hasQuery) {
      final code = uri.queryParameters['code'];
      if (code != null && code.isNotEmpty) value = code;
    }
    final hex = value.replaceAll(RegExp(r'[^a-fA-F0-9]'), '').toLowerCase();
    return hex.length == 64 ? hex : value;
  }

  static bool looksLikeToken(String value) => normalize(value).length == 64;

  static String newRequestId() => const Uuid().v4();
}
