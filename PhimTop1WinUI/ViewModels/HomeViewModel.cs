using CommunityToolkit.Mvvm.ComponentModel;
using System.Collections.ObjectModel;
using PhimTop1WinUI.Models;
using PhimTop1WinUI.Services;
using System.Threading.Tasks;

namespace PhimTop1WinUI.ViewModels
{
    public partial class HomeViewModel : ObservableObject
    {
        private readonly ApiService _api = new ApiService();

        [ObservableProperty]
        private ObservableCollection<Movie> movies = new ObservableCollection<Movie>();

        [ObservableProperty]
        private bool isLoading;

        public async Task LoadDataAsync()
        {
            IsLoading = true;
            var data = await _api.GetHomeMoviesAsync();
            Movies.Clear();
            foreach(var m in data) Movies.Add(m);
            IsLoading = false;
        }
    }
}