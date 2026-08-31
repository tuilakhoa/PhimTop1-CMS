using System;
using System.Net.Http;
using System.Threading.Tasks;
using Newtonsoft.Json;
using System.Collections.Generic;
using PhimTop1WinUI.Models;

namespace PhimTop1WinUI.Services
{
    public class ApiService
    {
        private readonly HttpClient _http = new HttpClient { BaseAddress = new Uri("https://phimtop1.com/api/v1/") };

        public async Task<List<Movie>> GetHomeMoviesAsync()
        {
            try
            {
                var response = await _http.GetStringAsync("home.php");
                // Simplified mock parsing for now
                return new List<Movie>(); 
            }
            catch { return new List<Movie>(); }
        }
    }
}