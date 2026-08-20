void main() {
  var uri = Uri.parse('phimtop1://movie/dao-quat-vuong-vua-trom-mo?utm=test');
  print('scheme: ${uri.scheme}');
  print('host: ${uri.host}');
  print('path: ${uri.path}');
  print('toString: ${uri.toString()}');
}
