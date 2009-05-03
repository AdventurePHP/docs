SELECT COUNT(*) AS EntriesCount
FROM comp_guestbook_entry
WHERE GuestbookID = '[GuestbookID]'
GROUP BY (GuestbookID);