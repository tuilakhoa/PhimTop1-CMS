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
        private ObservableCollection<Movie> latestMovies = new ObservableCollection<Movie>();

        [ObservableProperty]
        private ObservableCollection<Movie> featuredMovies = new ObservableCollection<Movie>();

        [ObservableProperty]
        private bool isLoading;

        public async Task LoadDataAsync()
        {
            IsLoading = true;
            var data = await _api.GetHomeDataAsync();
            
            LatestMovies.Clear();
            if (data.Items != null)
            {
                foreach(var m in data.Items) LatestMovies.Add(m);
            }

            FeaturedMovies.Clear();
            if (data.FeaturedMovies != null)
            {
                foreach(var m in data.FeaturedMovies) FeaturedMovies.Add(m);
            }

            IsLoading = false;
        }
    }
}
