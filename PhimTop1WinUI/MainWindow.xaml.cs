using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using PhimTop1WinUI.Views;

namespace PhimTop1WinUI
{
    public sealed partial class MainWindow : Window
    {
        public MainWindow()
        {
            this.InitializeComponent();
            this.Title = "PhimTop1";
            NavView.SelectedItem = NavView.MenuItems[0];
            ContentFrame.Navigate(typeof(HomePage));
        }

        private void NavView_SelectionChanged(NavigationView sender, NavigationViewSelectionChangedEventArgs args)
        {
            var item = args.SelectedItem as NavigationViewItem;
            if (item?.Tag?.ToString() == "Home")
            {
                ContentFrame.Navigate(typeof(HomePage));
            }
        }
    }
}