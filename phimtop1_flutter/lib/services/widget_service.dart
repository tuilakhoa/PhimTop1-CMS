import 'package:home_widget/home_widget.dart';
import '../models/models.dart';

class WidgetService {
  static Future<void> updateContinueWatchingWidget(List<HistoryItem> history) async {
    String text = "";
    if (history.isEmpty) {
      text = "Bạn chưa xem phim nào.";
    } else {
      for (int i = 0; i < history.length && i < 5; i++) {
        text += "▶ ${history[i].movieName} - ${history[i].episodeName}\n";
      }
    }
    
    await HomeWidget.saveWidgetData<String>('movies_list', text.trim());
    await HomeWidget.updateWidget(
      name: 'AppWidgetProvider',
      iOSName: 'AppWidgetProvider',
    );
  }
}
