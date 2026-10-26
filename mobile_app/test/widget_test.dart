import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_app/main.dart';

void main() {
  testWidgets('App launches with splash screen smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: AiLoopingApp(),
      ),
    );

    // Verify splash screen rendered
    expect(find.text('AI Looping'), findsOneWidget);
  });
}
