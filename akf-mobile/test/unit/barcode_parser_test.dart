import 'package:flutter_test/flutter_test.dart';
import 'package:yalla_events/core/utils/barcode_parser.dart';

void main() {
  const token = 'd85e9a17f1c8a88608a2a4fa9aae1f689bb5324d9558f743d70b2cf9a52f7d06';

  test('accepts raw token', () {
    expect(BarcodeParser.normalize(token), token);
  });

  test('accepts full verification URL', () {
    final url = 'https://arab-kulturfestival.de/?akf_verify=1&code=$token';
    expect(BarcodeParser.normalize(url), token);
  });

  test('strips hidden characters', () {
    final dirty = '\u200b $token \n';
    expect(BarcodeParser.normalize(dirty), token);
  });

  test('rejects short codes', () {
    expect(BarcodeParser.looksLikeToken('ABC123'), isFalse);
  });

  test('request ids are unique', () {
    expect(BarcodeParser.newRequestId(), isNot(BarcodeParser.newRequestId()));
  });
}
