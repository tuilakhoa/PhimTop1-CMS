import 'package:dio/dio.dart';
import 'lib/models/models.dart';

void main() async {
  try {
    final dio = Dio();
    final res = await dio.get('http://localhost:8080/api/v1/comments.php?slug=squid-game-phan-2&key=YOUR_KEY_IF_ANY');
    print(res.data);
    final commentRes = CommentResponse.fromJson(res.data);
    print("Success! comments: ${commentRes.data?.length}");
  } catch (e, st) {
    print("Error: $e");
    print(st);
  }
}
