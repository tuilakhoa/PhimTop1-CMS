using Microsoft.UI.Xaml.Controls;
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
    }
}