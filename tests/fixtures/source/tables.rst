
==========  ========================================  ==========================================
Route path  If the requested URL is /foo              If the requested URL is /foo/
==========  ========================================  ==========================================
/foo        It matches (200 status response)          It doesn't match (404 status response)
/foo/       It makes a 301 redirect to /foo/          It matches (200 status response)
==========  ========================================  ==========================================
