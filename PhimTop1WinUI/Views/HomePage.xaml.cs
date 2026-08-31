using Microsoft.UI.Xaml.Controls;
using PhimTop1WinUI.Models;
using PhimTop1WinUI.ViewModels;

namespace PhimTop1WinUI.Views
{
    public sealed partial class HomePage : Page
    {
        public HomeViewModel ViewModel { get; } = new HomeViewModel();

        public HomePage()
        {
            this.InitializeComponent();
            _ = ViewModel.LoadDataAsync();
        }

        private void GridView_ItemClick(object sender, ItemClickEventArgs e)
        {
            if (e.ClickedItem is Movie movie)
            {
                Frame.Navigate(typeof(MovieDetailPage), movie);
            }
        }
    }
}
