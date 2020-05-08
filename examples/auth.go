package main

import (
	"io"
	"log"
	"net/http"
)

func main() {
	// Hello world, the web server
	helloHandler := func(w http.ResponseWriter, req *http.Request) {
		if req.ParseForm() == nil && req.Form.Get("secret") == "secret" {
			io.WriteString(w, `{
				"ttl": 3600,
				"identity": "username",
				"identity_url": "https://....",
				"authorizations": [
					{
						"permissions": [
							"subscribe",
							"publish"
						],
						"topic": ".*",
						"channels": [
							".*"
						]
					}
				]
			}`)
		}
	}

	http.HandleFunc("/auth", helloHandler)
	log.Fatal(http.ListenAndServe(":8000", nil))
}