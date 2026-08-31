using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using Microsoft.UI.Xaml.Navigation;
using Microsoft.UI.Xaml.Media.Imaging;
using PhimTop1WinUI.Models;
using System;

namespace PhimTop1WinUI.Views
{
    public sealed partial class MovieDetailPage : Page
    {
        private Movie _movie;

        public MovieDetailPage()
        {
            this.InitializeComponent();
        }

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            if (e.Parameter is Movie movie)
            {
                _movie = movie;
                TitleText.Text = movie.Name;
                OriginTitleText.Text = $"{movie.OriginName} ({movie.Year})";
                try {
                    PosterImage.Source = new BitmapImage(new Uri(movie.FullThumbUrl));
                } catch {}
            }
        }

        private void BackButton_Click(object sender, RoutedEventArgs e)
        {
            if (Frame.CanGoBack)
            {
                Frame.GoBack();
            }
        }
    }
}
