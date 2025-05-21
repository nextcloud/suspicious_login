OC.L10N.register(
    "suspicious_login",
    {
    "New login location detected" : "新しいログイン場所が検出されました",
    "More info about the suspicious IP address available on %s" : "不審なIPアドレスについての詳細情報は %s で入手できます",
    "A new login into your account was detected. The IP address %s was classified as suspicious. If this was you, you can ignore this message. Otherwise you should change your password." : "アカウントへの新しいログインが検出されました。 IP アドレス%sは不審なログインに分類されます。このログインがあなたであればこのメッセージは無視して構いません。そうでない場合は、パスワードを変更してください。",
    "Open %s ↗" : "%s を開く ↗",
    "Suspicious Login" : "不審なログイン",
    "New login detected" : "新しいログインが検出されました",
    "Detect and warn about suspicious IPs logging into Nextcloud\n\t" : "Nextcloudにログインする不審なIPを検出して警告します\n\t",
    "Suspicious login detection" : "不審なログインの検出",
    "The suspicious login app is enabled on this instance. It will keep track of IP addresses users successfully log in from and build a classifier that warns if a new login comes from a suspicious IP address." : "このインスタンスで不審ログインの検知アプリケーションが有効になっています。ユーザーが正常にログインしたIPアドレスを追跡し、疑わしいIPアドレスから新しいログインがあった場合に警告する分類モデルを構築します。",
    "Training data statistics" : "トレーニングデータの統計",
    "So far the app has captured {total} logins (including client connections), of which {distinct} are distinct (IP, UID) tuples." : "これまで、アプリは {total} 件のログイン(クライアント接続を含む)を記録しており、その中で重複しない (IP, UID) の組み合わせは {distinct} 個あります。",
    "IPv4" : "IPv4",
    "IPv6" : "IPv6",
    "Classifier model statistics" : "分類モデルの統計",
    "No classifier model has been trained yet. This most likely means that you just enabled the app recently. Because the training of a model requires good data, the app waits until logins of at least {days} days have been captured." : "分類モデルはまだトレーニングされていません。これはおそらく、最近アプリを有効にしたばかりであることを意味します。モデルを正しくトレーニングするには良いデータが必要なので、アプリは少なくとも {days} 日間のログイン情報が記録されるまで待機します。",
    "During evaluation, the latest model (trained {time}) has shown to capture {recall}% of all suspicious logins (recall), whereas {precision}% of the logins classified as suspicious are indeed suspicious (precision). Below you see a visualization of historic model performance." : "評価中、最新のモデル ({time}にトレーニングされたもの) は全ての疑わしいログインのうち {recall}% を捕捉しました(再現率)。一方で、疑わしいと分類したログインの {precision}% は実際に疑わしいものです(適合率)。次の図は、モデルのパフォーマンスの履歴を視覚化したものです。",
    "Precision" : "適合率",
    "Recall" : "再現率"
},
"nplurals=1; plural=0;");
