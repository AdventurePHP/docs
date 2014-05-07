SELECT ArticleCommentID AS DB_ID
FROM article_comments
WHERE CategoryKey = '[CategoryKey]'
ORDER BY Date DESC, Time DESC
LIMIT [Start],[EntriesCount];