vcl 4.0;

backend default {
    .host = "127.0.0.1";
    .port = "8082";
}

sub vcl_recv {
    return (pass);
}

sub vcl_backend_response {
    set beresp.ttl = 0s;
    set beresp.uncacheable = true;
}

sub vcl_deliver {
    set resp.http.X-Varnish-Disabled = true;
}