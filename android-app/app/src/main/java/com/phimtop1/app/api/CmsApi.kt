package com.phimtop1.app.api

import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Header
import retrofit2.http.Query
import retrofit2.http.Body
import retrofit2.http.Url

// Models mapping to your CMS API
data class ApiResponse<T>(
    val status: String,
    val data: T
)

data class AppInitResponse(
    val status: String,
    val data: AppInitData
)

data class AppInitData(
    val siteName: String,
    val logoUrl: String,
    val maintenance: Boolean,
    val appBannerEnabled: Int,
    val appDownloadUrl: String,
    val enableComics: Int,
    val isComicPluginActive: Boolean?,
    val version: String
)

data class HomeData(
    val items: List<MovieItem>,
    val titlePage: String,
    val domain: String,
    val featuredMovies: List<MovieItem>? = null,
    val featuredStyle: String? = null
)

data class MovieItem(
    val _id: String?,
    val name: String,
    val slug: String,
    val origin_name: String?,
    val thumb_url: String?,
    val poster_url: String?,
    val year: Int?
)

data class CategoryData(
    val items: List<CategoryItem>,
    val titlePage: String,
    val domain: String
)

data class CategoryItem(
    val slug: String,
    val name: String,
    val type: String
)

data class MovieDetailData(
    val domain: String,
    val movie: MovieDetail,
    val episodes: List<Episode>?
)

data class MovieDetail(
    val _id: String?,
    val name: String,
    val slug: String,
    val origin_name: String?,
    val thumb_url: String?,
    val poster_url: String?,
    val year: Int?,
    val content: String?,
    val actor: List<String>?,
    val time: String?,
    val episode_current: String?
)

data class Episode(
    val server_name: String,
    val server_data: List<ServerData>
)

data class ServerData(
    val name: String,
    val slug: String,
    val filename: String,
    val link_embed: String,
    val link_m3u8: String
)

data class ComicDetailData(
    val domain: String,
    val comic: MovieDetail,
    val chapters: List<ComicChapter>?
)

data class ComicChapter(
    val server_name: String,
    val server_data: List<ComicChapterData>
)

data class ComicChapterData(
    val filename: String,
    val chapter_name: String,
    val chapter_api_data: String,
    val slug: String
)

data class LoginRequest(
    val email: String,
    val password: String
)

data class RegisterRequest(
    val name: String,
    val email: String,
    val password: String
)

data class FollowItem(
    val id: Int,
    val user_email: String,
    val item_slug: String,
    val item_type: String,
    val item_name: String,
    val thumb_url: String?,
    val created_at: String
)

data class FollowResponse(
    val status: String,
    val data: List<FollowItem>
)

data class CheckFollowResponse(
    val status: String,
    val is_following: Boolean
)

data class ToggleFollowRequest(
    val item_slug: String,
    val item_type: String,
    val item_name: String,
    val thumb_url: String?
)

data class ToggleFollowResponse(
    val status: String,
    val action: String
)

data class NotificationItem(
    val id: Int,
    val user_email: String?,
    val title: String,
    val message: String,
    val url: String?,
    val is_read: Int,
    val created_at: String
)

data class NotificationResponse(
    val status: String,
    val data: List<NotificationItem>
)

data class MarkReadRequest(
    val notification_id: Int
)

data class AuthResponse(
    val status: String,
    val message: String?,
    val token: String?,
    val user: User?
)

data class User(
    val id: String,
    val name: String,
    val email: String,
    val avatar: String?
)

data class OtruyenChapterResponse(
    val status: String,
    val message: String,
    val data: OtruyenChapterData
)

data class OtruyenChapterData(
    val domain_cdn: String,
    val item: OtruyenChapterItem
)

data class OtruyenChapterItem(
    val chapter_path: String,
    val chapter_image: List<OtruyenChapterImage>
)

data class OtruyenChapterImage(
    val image_page: Int,
    val image_file: String
)

data class CommentItem(
    val id: Int,
    val user_name: String,
    val content: String,
    val time_ago: String
)

data class CommentResponse(
    val success: Boolean,
    val message: String?,
    val data: List<CommentItem>?
)

data class PostCommentRequest(
    val slug: String,
    val name: String,
    val content: String,
    val anonymous: Boolean
)

data class PostCommentResponse(
    val success: Boolean,
    val message: String?,
    val comment: CommentItem?
)

interface CmsApiService {
    @GET("api/v1/app_init.php")
    suspend fun getAppInit(
        @Query("key") apiKey: String
    ): AppInitResponse

    @GET("api/v1/movie.php")
    suspend fun getMovieDetail(
        @Query("key") apiKey: String,
        @Query("slug") slug: String
    ): ApiResponse<MovieDetailData>

    @GET("api/v1/home.php")
    suspend fun getHome(
        @Query("key") apiKey: String,
        @Query("page") page: Int = 1
    ): ApiResponse<HomeData>

    @GET("api/v1/search.php")
    suspend fun searchMovies(
        @Query("key") apiKey: String,
        @Query("keyword") keyword: String,
        @Query("page") page: Int = 1
    ): ApiResponse<HomeData>

    @GET("api/v1/categories.php")
    suspend fun getCategories(
        @Query("key") apiKey: String
    ): ApiResponse<CategoryData>

    @GET("api/v1/category.php")
    suspend fun getCategory(
        @Query("key") apiKey: String,
        @Query("type") type: String,
        @Query("slug") slug: String,
        @Query("page") page: Int = 1
    ): ApiResponse<HomeData>

    @GET("api/v1/comics.php")
    suspend fun getComics(
        @Query("key") apiKey: String,
        @Query("type") type: String = "danh-sach",
        @Query("page") page: Int = 1
    ): ApiResponse<HomeData>

    @GET("api/v1/comics.php")
    suspend fun getComicDetail(
        @Query("key") apiKey: String,
        @Query("action") action: String = "detail",
        @Query("slug") slug: String
    ): ApiResponse<ComicDetailData>

    @POST("api/v1/auth.php?action=login")
    suspend fun login(
        @Query("key") apiKey: String,
        @Body request: LoginRequest
    ): AuthResponse

    @POST("api/v1/auth.php?action=register")
    suspend fun register(
        @Query("key") apiKey: String,
        @Body request: RegisterRequest
    ): AuthResponse

    @GET("api/v1/follow.php?action=list")
    suspend fun getFollows(
        @Query("key") apiKey: String,
        @Header("Authorization") token: String,
        @Query("type") type: String
    ): FollowResponse

    @GET("api/v1/follow.php?action=check")
    suspend fun checkFollow(
        @Query("key") apiKey: String,
        @Header("Authorization") token: String,
        @Query("slug") slug: String
    ): CheckFollowResponse

    @POST("api/v1/follow.php?action=toggle")
    suspend fun toggleFollow(
        @Query("key") apiKey: String,
        @Header("Authorization") token: String,
        @Body request: ToggleFollowRequest
    ): ToggleFollowResponse

    @GET("api/v1/notifications.php?action=list")
    suspend fun getNotifications(
        @Query("key") apiKey: String,
        @Header("Authorization") token: String
    ): NotificationResponse

    @POST("api/v1/notifications.php?action=mark_read")
    suspend fun markNotificationsRead(
        @Query("key") apiKey: String,
        @Header("Authorization") token: String,
        @Body request: MarkReadRequest
    ): ApiResponse<String>

    @GET("api/v1/comments.php")
    suspend fun getComments(
        @Query("key") apiKey: String,
        @Query("slug") slug: String
    ): CommentResponse

    @POST("api/v1/comments.php")
    suspend fun postComment(
        @Query("key") apiKey: String,
        @Header("Authorization") token: String,
        @Body request: PostCommentRequest
    ): PostCommentResponse

    // Direct call to OTruyen API for chapter images
    @GET
    suspend fun getChapterImages(@Url url: String): OtruyenChapterResponse
}

object RetrofitClient {
    // Change this to your actual website domain when building
    const val BASE_URL = "https://yourdomain.com/"
    
    // IMPORTANT: Replace this with the App API Key from your CMS Admin -> Cài đặt App
    const val API_KEY = "your-api-key"

    val instance: CmsApiService by lazy {
        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        retrofit.create(CmsApiService::class.java)
    }
}
