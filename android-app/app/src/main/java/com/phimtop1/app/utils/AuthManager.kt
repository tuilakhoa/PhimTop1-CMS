package com.phimtop1.app.utils

import android.content.Context
import android.content.SharedPreferences

class AuthManager(context: Context) {
    private val prefs: SharedPreferences = context.getSharedPreferences("auth_prefs", Context.MODE_PRIVATE)

    fun saveToken(token: String) {
        prefs.edit().putString("auth_token", token).apply()
    }

    fun getToken(): String? {
        return prefs.getString("auth_token", null)
    }

    fun saveUser(id: String, name: String, email: String, avatar: String?) {
        prefs.edit()
            .putString("user_id", id)
            .putString("user_name", name)
            .putString("user_email", email)
            .putString("user_avatar", avatar)
            .apply()
    }

    fun getUser(): UserInfo? {
        val id = prefs.getString("user_id", null)
        val name = prefs.getString("user_name", null)
        val email = prefs.getString("user_email", null)
        if (id != null && name != null && email != null) {
            return UserInfo(id, name, email, prefs.getString("user_avatar", null))
        }
        return null
    }

    fun clear() {
        prefs.edit().clear().apply()
    }

    fun isLoggedIn(): Boolean {
        return getToken() != null
    }
}

data class UserInfo(
    val id: String,
    val name: String,
    val email: String,
    val avatar: String?
)
