using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;

namespace PhimTop1WinUI
{
    public sealed partial class MainWindow : Window
    {
        public MainWindow()
        {
            this.InitializeComponent();
            this.Title = "PhimTop1";
        }

        private void myButton_Click(object sender, RoutedEventArgs e)
        {
            myButton.Content = "Loading movies...";
        }
    }
}