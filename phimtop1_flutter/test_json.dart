import 'dart:convert';
import 'lib/models/models.dart';

void main() {
  String jsonStr = '{"success":true,"data":[{"id":"123","user_name":"Test","content":"Test","time_ago":"Vừa xong"}]}';
  try {
    var decoded = json.decode(jsonStr);
    var res = CommentResponse.fromJson(decoded);
    print('Success: ${res.data?.length}');
  } catch (e, stack) {
    print('Error: $e');
    print(stack);
  }
}
