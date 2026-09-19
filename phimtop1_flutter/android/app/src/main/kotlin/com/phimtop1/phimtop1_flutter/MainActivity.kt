package com.phimtop1.phimtop1_flutter

import android.content.Intent
import android.content.res.Configuration
import android.speech.RecognizerIntent
import androidx.annotation.NonNull
import io.flutter.embedding.android.FlutterFragmentActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel
import cl.puntito.simple_pip_mode.PipCallbackHelper

class MainActivity : FlutterFragmentActivity() {
    private var callbackHelper = PipCallbackHelper()
    private val SPEECH_CHANNEL = "com.phimtop1.app/speech"
    private var speechResult: MethodChannel.Result? = null

    override fun configureFlutterEngine(@NonNull flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        callbackHelper.configureFlutterEngine(flutterEngine)
        
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, SPEECH_CHANNEL).setMethodCallHandler { call, result ->
            if (call.method == "startVoiceSearch") {
                speechResult = result
                val intent = Intent(RecognizerIntent.ACTION_RECOGNIZE_SPEECH)
                intent.putExtra(RecognizerIntent.EXTRA_LANGUAGE_MODEL, RecognizerIntent.LANGUAGE_MODEL_FREE_FORM)
                intent.putExtra(RecognizerIntent.EXTRA_LANGUAGE, "vi-VN")
                intent.putExtra(RecognizerIntent.EXTRA_PROMPT, "Đọc tên phim bạn muốn tìm...")
                try {
                    startActivityForResult(intent, 100)
                } catch (e: Exception) {
                    result.error("SPEECH_NOT_SUPPORTED", "Thiết bị không hỗ trợ Google Voice Search", null)
                }
            } else {
                result.notImplemented()
            }
        }
    }

    override fun onPictureInPictureModeChanged(active: Boolean, newConfig: Configuration) {
        super.onPictureInPictureModeChanged(active, newConfig)
        callbackHelper.onPictureInPictureModeChanged(active)
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == 100) {
            if (resultCode == RESULT_OK && data != null) {
                val res: ArrayList<String>? = data.getStringArrayListExtra(RecognizerIntent.EXTRA_RESULTS)
                if (res != null && res.isNotEmpty()) {
                    speechResult?.success(res[0])
                } else {
                    speechResult?.success("")
                }
            } else {
                speechResult?.success("")
            }
            speechResult = null
        }
    }
}
