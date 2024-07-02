{{- define "tgApp.appSelectorLabels"}}
app.kubernetes.io/name: {{ .Chart.Name }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{- define "tgApp.redisSelectorLabels"}}
app.kubernetes.io/name: {{ .Chart.Name }}-redis
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{- define "tgApp.redisName"}}
name: {{ .Chart.Name }}-redis
{{- end }}
