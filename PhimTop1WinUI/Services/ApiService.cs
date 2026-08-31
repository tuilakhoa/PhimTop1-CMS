using System;
using System.Net.Http;
using System.Threading.Tasks;
using Newtonsoft.Json;
using PhimTop1WinUI.Models;

namespace PhimTop1WinUI.Services
{
    public class ApiService
    {
        private readonly HttpClient _http = new HttpClient { BaseAddress = new Uri("https://phimtop1.com/api/v1/") };

        public async Task<HomeData> GetHomeDataAsync()
        {
            try
            {
                var json = await _http.GetStringAsync("home.php");
                var response = JsonConvert.DeserializeObject<ApiResponse<HomeData>>(json);
                if (response != null && response.Status == "success")
                {
                    return response.Data;
                }
                return new HomeData { Items = new System.Collections.Generic.List<Movie>() };
            }
            catch (Exception ex) 
            { 
                System.Diagnostics.Debug.WriteLine(ex.Message);
                return new HomeData { Items = new System.Collections.Generic.List<Movie>() }; 
            }
        }
    }
}
