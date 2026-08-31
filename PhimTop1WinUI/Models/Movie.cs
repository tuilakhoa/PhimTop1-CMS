namespace PhimTop1WinUI.Models
{
    public class Movie
    {
        public string Id { get; set; }
        public string Name { get; set; }
        public string OriginName { get; set; }
        public string ThumbUrl { get; set; }
        public string PosterUrl { get; set; }
        public string Slug { get; set; }
        public string Year { get; set; }
    }
    
    public class ApiResponse<T>
    {
        public bool Status { get; set; }
        public string Message { get; set; }
        public T Data { get; set; }
    }
}