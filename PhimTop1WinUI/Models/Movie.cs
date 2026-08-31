using Newtonsoft.Json;

namespace PhimTop1WinUI.Models
{
    public class Movie
    {
        [JsonProperty("_id")]
        public string Id { get; set; }

        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("origin_name")]
        public string OriginName { get; set; }

        [JsonProperty("thumb_url")]
        public string ThumbUrl { get; set; }

        [JsonProperty("poster_url")]
        public string PosterUrl { get; set; }

        [JsonProperty("slug")]
        public string Slug { get; set; }

        [JsonProperty("year")]
        public string Year { get; set; }

        public string FullThumbUrl => ThumbUrl?.StartsWith("http") == true ? ThumbUrl : $"https://phimimg.com/{ThumbUrl}";
        public string FullPosterUrl => PosterUrl?.StartsWith("http") == true ? PosterUrl : $"https://phimimg.com/{PosterUrl}";
    }

    public class HomeData
    {
        [JsonProperty("items")]
        public System.Collections.Generic.List<Movie> Items { get; set; }

        [JsonProperty("featuredMovies")]
        public System.Collections.Generic.List<Movie> FeaturedMovies { get; set; }
    }

    public class ApiResponse<T>
    {
        [JsonProperty("status")]
        public string Status { get; set; }

        [JsonProperty("data")]
        public T Data { get; set; }
    }
}
