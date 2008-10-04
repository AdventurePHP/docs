SELECT entry.EntryID AS DB_ID FROM entry
INNER JOIN comp_guestbook_entry ON entry.EntryID = comp_guestbook_entry.EntryID
INNER JOIN guestbook ON comp_guestbook_entry.GuestbookID = guestbook.GuestbookID
WHERE guestbook.GuestbookID = '[GuestbookID]'
ORDER BY Date DESC, Time DESC
LIMIT [Start],[EntriesCount];
